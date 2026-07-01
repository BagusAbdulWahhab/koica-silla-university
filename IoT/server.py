
# Simple MQTT server: listens to a topic and prints received messages
import datetime
import uuid
import time

import paho.mqtt.client as mqtt
import sqlite3

BROKER_URL = "broker.emqx.io"
PORT = 1883
TOPIC = "koica-iot-f7777-GTS" # topic for Gateway -> Server
TOPIC_SYNC = "koica-iot-f7777-SYNC"

def get_devices_by_gateway():
    conn = sqlite3.connect('koica-iot.db')
    cursor = conn.cursor()
    cursor.execute("""
        SELECT id, gw_id
        FROM devices
    """)
    rows = cursor.fetchall()
    conn.close()
    gateways = {}
    for device_id, gw_id in rows:
        if gw_id not in gateways:
            gateways[gw_id] = []
        gateways[gw_id].append(device_id)
    return gateways

# -----------
# SQLite functions
# -----------
def is_device_registered(device_id):
  conn = sqlite3.connect('koica-iot.db')
  cursor = conn.cursor()
  cursor.execute('SELECT 1 FROM devices WHERE id = ?', (device_id,))
  result = cursor.fetchone()
  conn.close()
  return result is not None

def save_device_data(device_id, temp, hum, lux, noise):
  msg_id = uuid.uuid4().hex
  timestamp = datetime.datetime.now().isoformat()

  conn = sqlite3.connect('koica-iot.db')
  cursor = conn.cursor()
  cursor.execute('''
      INSERT INTO device_data (id, device_id, temp, hum, lux, noise, timestamp)
      VALUES (?, ?, ?, ?, ?, ?, ?)
      ON CONFLICT(id) DO NOTHING
  ''', (msg_id, device_id, temp, hum, lux, noise, timestamp))
  conn.commit()
  conn.close()

# -----------
# MQTT functions
# -----------
def on_connect(client, userdata, flags, rc):
  if rc == 0:
    print("Connected to MQTT Broker!")
    client.subscribe(TOPIC)
    print("Subscribed to topic:", TOPIC)
  else:
    print("Failed to connect, return code", rc)

def on_message(client, userdata, message):
  msg = message.payload.decode()
  print(f"Received message: {msg} (topic: {message.topic})")

  try:
    # parse the message
    msg_decoded = msg.split(";")
    device_id = msg_decoded[0]
    temp = float(msg_decoded[1])
    hum = float(msg_decoded[2])
    lux = float(msg_decoded[3])
    noise = float(msg_decoded[4])

    # Save to SQLite database
    save_device_data(device_id, temp, hum, lux, noise)
    print(f"Saved aggregated data for device {device_id} to database.")
  except Exception as e:
    print(f"Error parsing/saving message: {e}")

client = mqtt.Client()
client.on_connect = on_connect
client.on_message = on_message
client.connect(BROKER_URL, PORT)
client.loop_start()

try:
    while True:
        print("\n[SYNC] Loading devices from database...")
        gateways = get_devices_by_gateway()
        # Mengirim Device berdasarkan gateway
        for gw_id, devices in gateways.items():
            msg = f"SYNC;{gw_id};" + ";".join(devices)
            client.publish(TOPIC_SYNC, msg)
            print(f"[SYNC SENT] {msg}")
        print("[SYNC] Complete")
        time.sleep(15)
        
except KeyboardInterrupt:
    print("Disconnecting from the broker...")

client.loop_stop()
client.disconnect()
