
# Simple MQTT device: hardcoded ID, sends random data periodically
import argparse
import paho.mqtt.client as mqtt
import random
import time

BROKER_URL = "broker.emqx.io"
PORT = 1883
TOPIC_DTG = "koica-iot-f7777-DTG" # contains "DATA"
TOPIC_GTD = "koica-iot-f7777-GTD" # contains "CMD"

def parse_args():
  parser = argparse.ArgumentParser(description="Simple MQTT device")
  parser.add_argument(
    "device_id",
    help="Unique device identifier used in outgoing messages",
  )
  return parser.parse_args()

args = parse_args()
DEVICE_ID = args.device_id

def on_connect(client, userdata, flags, rc):
  if rc == 0:
    print("Connected to MQTT Broker!")
    client.subscribe(TOPIC_GTD)
    print("Subscribed to topic:", TOPIC_GTD)
  else:
    print("Failed to connect, return code", rc)

def on_message(client, userdata, message):
  msg = message.payload.decode()
  msg_decoded = msg.split(";");
  if(msg_decoded[0]=="CMD" and msg_decoded[1]=="SEND_DATA"):
    print(f"Received command: {msg_decoded[1]}")
    temp = random.randint(20, 30)
    hum = random.randint(30, 60)
    lux = random.randint(100, 500)
    noise = random.randint(25, 55)
    msg = f"{DEVICE_ID};{temp};{hum};{lux};{noise}"

    result = client.publish(TOPIC_DTG, msg)

    status = result[0]
    if status == 0:
      print(f"Sent: {msg}")
    else:
      print("Failed to send message")

client = mqtt.Client()
client.on_connect = on_connect
client.on_message = on_message
client.connect(BROKER_URL, PORT)
client.loop_start()

try:
  while True:
    pass
except KeyboardInterrupt:
  print("Disconnecting from the broker...")

client.loop_stop()
client.disconnect()
