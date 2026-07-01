import paho.mqtt.client as mqtt
import time
import sqlite3
import uuid
from datetime import datetime

# MQTT broker settings
BROKER_URL = "broker.emqx.io"
PORT = 1883

TOPIC_GTD = "koica-iot-f7777-GTD"   # Gateway -> Device
TOPIC_DTG = "koica-iot-f7777-DTG"   # Device -> Gateway
TOPIC_GTS = "koica-iot-f7777-GTS"   # Gateway -> Server
TOPIC_SYNC = "koica-iot-f7777-SYNC"

GATEWAY_ID = "gw_001"

# Buffer agregasi sementara
devices_temp_data = {}


def is_registered(device_id):
    conn = sqlite3.connect("gateway.db")
    cursor = conn.cursor()

    cursor.execute("""
        SELECT id
        FROM devices
        WHERE id = ?
    """, (device_id,))

    result = cursor.fetchone()

    conn.close()

    return result is not None


def sync_devices_to_db(gw_id, devices):
    conn = sqlite3.connect("gateway.db")
    cursor = conn.cursor()

    cursor.execute("""
        DELETE FROM devices
        WHERE gw_id = ?
    """, (gw_id,))

    for device in devices:
        cursor.execute("""
            INSERT INTO devices(id, description, gw_id)
            VALUES (?, ?, ?)
        """, (
            device,
            "Synced from server",
            gw_id
        ))

    conn.commit()
    conn.close()


def save_local_data(device_id, temp, hum, lux, noise):
    msg_id = uuid.uuid4().hex
    timestamp = datetime.now().isoformat()

    conn = sqlite3.connect("gateway.db")
    cursor = conn.cursor()
    cursor.execute("""
        INSERT INTO device_data (id, device_id, temp, hum, lux, noise, timestamp)
        VALUES (?, ?, ?, ?, ?, ?, ?)
    """, (msg_id, device_id, temp, hum, lux, noise, timestamp))
    conn.commit()
    conn.close()


def init_device_buffer(device_id):
    if device_id not in devices_temp_data:
        devices_temp_data[device_id] = {
            "temp": 0,
            "hum": 0,
            "lux": 0,
            "noise": 0,
            "count": 0
        }


# MQTT callbacks
def on_connect(client, userdata, flags, rc):
    if rc == 0:
        print("Connected to MQTT Broker!")

        client.subscribe(TOPIC_DTG)
        client.subscribe(TOPIC_SYNC)

        print(f"Subscribed to: {TOPIC_DTG}")
        print(f"Subscribed to: {TOPIC_SYNC}")

    else:
        print("Failed to connect:", rc)


def on_message(client, userdata, message):
    print(f"[MQTT] Topic={message.topic}")
    print(f"[MQTT] Payload={message.payload.decode()}")

    msg = message.payload.decode()

    # ======================
    # SYNC MESSAGE
    # ======================
    if message.topic == TOPIC_SYNC:

        msg_decoded = msg.split(";")

        if len(msg_decoded) < 2:
            print("[SYNC ERROR] Invalid format")
            return

        gw_id = msg_decoded[1]

        if gw_id != GATEWAY_ID:
            return

        print(f"\n[SYNC RECEIVED] {msg}")

        device_list = msg_decoded[2:]

        sync_devices_to_db(gw_id, device_list)

        # Siapkan buffer agregasi
        for device_id in device_list:
            init_device_buffer(device_id)

        print("[SYNC] Gateway database updated")

        return

    # ======================
    # SENSOR DATA MESSAGE
    # ======================
    try:
        msg_decoded = msg.split(";")

        if len(msg_decoded) != 5:
            print("[ERROR] Invalid payload format")
            return

        device_id = msg_decoded[0]
        temp = int(msg_decoded[1])
        hum = int(msg_decoded[2])
        lux = int(msg_decoded[3])
        noise = int(msg_decoded[4])

    except Exception as e:
        print(f"[ERROR] Failed parsing message: {e}")
        return

    # Authentication
    if not is_registered(device_id):
        print(f"[AUTH FAILED] {device_id}")
        return

    print(f"[AUTH SUCCESS] {device_id}")

    # Simpan data mentah ke gateway database
    save_local_data(device_id, temp, hum, lux, noise)

    # Pastikan buffer ada
    init_device_buffer(device_id)

    # Aggregation
    devices_temp_data[device_id]["temp"] += temp
    devices_temp_data[device_id]["hum"] += hum
    devices_temp_data[device_id]["lux"] += lux
    devices_temp_data[device_id]["noise"] += noise
    devices_temp_data[device_id]["count"] += 1

    count = devices_temp_data[device_id]["count"]

    print(f"Received from {device_id}, count: {count}")

    # Kirim rata-rata tiap 5 data
    if count >= 5:

        avg_temp = devices_temp_data[device_id]["temp"] / count
        avg_hum = devices_temp_data[device_id]["hum"] / count
        avg_lux = devices_temp_data[device_id]["lux"] / count
        avg_noise = devices_temp_data[device_id]["noise"] / count

        avg_msg = (
            f"{device_id};"
            f"{avg_temp:.2f};"
            f"{avg_hum:.2f};"
            f"{avg_lux:.2f};"
            f"{avg_noise:.2f}"
        )

        client.publish(TOPIC_GTS, avg_msg)

        print(f"[EDGE] Sent to server: {avg_msg}")

        # Reset buffer
        devices_temp_data[device_id] = {
            "temp": 0,
            "hum": 0,
            "lux": 0,
            "noise": 0,
            "count": 0
        }


# ======================
# MAIN
# ======================

client = mqtt.Client()
client.on_connect = on_connect
client.on_message = on_message

client.connect(BROKER_URL, PORT)

client.loop_start()

try:
    while True:

        # Request data ke device
        client.publish(TOPIC_GTD, "CMD;SEND_DATA")

        time.sleep(5)

except KeyboardInterrupt:
    print("\nDisconnecting from broker...")

finally:
    client.loop_stop()
    client.disconnect()