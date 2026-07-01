# IoT Edge Gateway Authentication using MQTT

## Overview

This project demonstrates an IoT architecture that shifts device
authentication from a centralized server to an edge gateway. Instead of
validating every device request against the server, each gateway
maintains a synchronized local device registry (`gateway.db`) that is
periodically updated from the server (`koica-iot.db`).

The implementation uses MQTT for communication and SQLite for data
storage. Besides authenticating devices locally, the gateway also
performs edge data aggregation by averaging sensor readings before
forwarding them to the server.

## Motivation

In a conventional IoT architecture, every sensor data transmission
requires the gateway to query the server for device authentication. This
project introduces distributed authentication using a synchronized local
database at the gateway.

## Key Features

-   MQTT Publish/Subscribe communication
-   Distributed device authentication
-   Device registry synchronization
-   Local SQLite database on the gateway
-   Edge data aggregation (5 sensor readings -\> 1 averaged record)
-   Multi-gateway support using `gw_id`

## Architecture

``` text
Server (koica-iot.db)
        |
   MQTT SYNC
        |
Gateway (gateway.db)
        |
Local Authentication
        |
      Device
        |
 Edge Aggregation
        |
      Server
```

## Project Structure

``` text
.
├── device.py
├── gateway.py
├── server.py
├── init_db.py
├── init_gateway_db.py
├── koica-iot.db
├── gateway.db
└── README.md
```

## MQTT Topics

  Topic                  Direction          Purpose
  ---------------------- ------------------ ------------------------
  koica-iot-f7777-GTD    Gateway → Device   Request sensor data
  koica-iot-f7777-DTG    Device → Gateway   Sensor data
  koica-iot-f7777-GTS    Gateway → Server   Aggregated data
  koica-iot-f7777-SYNC   Server → Gateway   Device synchronization

## Workflow

1.  Server reads registered devices from `koica-iot.db`.
2.  Server groups devices by `gw_id`.
3.  Server synchronizes the registry to the corresponding gateway.
4.  Gateway updates `gateway.db`.
5.  Device sends sensor readings.
6.  Gateway authenticates the device using `gateway.db`.
7.  Gateway aggregates every 5 sensor readings.
8.  Gateway forwards the aggregated result to the server.

## Installation

``` bash
pip install paho-mqtt
python init_db.py
python init_gateway_db.py
python server.py
python gateway.py
python device.py
```

## Future Improvements

-   TLS encryption
-   Device digital signature
-   QoS support
-   Automatic synchronization
-   Monitoring dashboard

## License

Educational and research purposes.
