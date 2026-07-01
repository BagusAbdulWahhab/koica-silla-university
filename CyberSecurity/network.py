import socket
import threading
import json

HOST = "127.0.0.1"
PORT = 9000

devices = {}
lock = threading.Lock()


def send_json(conn, data):
    conn.sendall(
        (json.dumps(data) + "\n").encode()
    )


def broadcast(sender_mac, message):
    with lock:
        sender_name = devices[sender_mac]["name"]
        print(
            f"\n[NETWORK] "
            f"Broadcast from "
            f"{sender_name}"
        )
        for mac, device in devices.items():
            if mac == sender_mac:
                continue
            try:
                send_json(device["conn"],message)
                print(
                    f" -> delivered to "
                    f"{device['name']}"
                )
            except Exception as e:
                print(f"Failed: {e}")

def unicast(destination_mac,message):
    with lock:
        if (destination_mac not in devices):
            print(
                f"\n[NETWORK] "
                f"Unknown MAC: "
                f"{destination_mac}"
            )
            return

        device = devices[destination_mac]
        try:
            send_json(device["conn"],message)
            print(
                f"\n[NETWORK] "
                f"Unicast → "
                f"{device['name']}"
            )
        except Exception as e:
            print(
                f"Failed: {e}"
            )


def handle_client(conn):
    mac = None
    buffer = ""
    try:
        while True:
            data = conn.recv(1024)
            if not data:
                break
            buffer += (data.decode())
            while (
                "\n"
                in buffer
            ):
                line, buffer = (
                    buffer.split(
                        "\n",
                        1
                    )
                )
                if (not line.strip()):
                    continue
                message = (json.loads(line))
                msg_type = (message["type"])

                if (msg_type== "REGISTER"):
                    mac = (message["mac"])
                    with lock:
                        devices[mac] = {
                            "name":message["name"],
                            "ip":message["ip"],
                            "conn":conn
                        }
                    print(
                        f"\n[NETWORK] "
                        f"{message['name']} "
                        f"registered"
                    )
                elif (msg_type== "ARP_REQUEST"):
                    broadcast(message["sender_mac"],message)
                elif (msg_type== "UNICAST"):
                    unicast(message["destination_mac"],message)
                #elif msg_type == "UNICAST":
                    #destination_mac = message["destination"]
                    #unicast(destination_mac,message)
                elif(msg_type == "ARP_REPLAY"):
                    destination_mac = message ["destination_mac"]
                    unicast(destination_mac,message)

    except Exception as e:
        print(
            f"\n[NETWORK] "
            f"Error: {e}"
        )

    finally:
        if mac:
            with lock:
                if (mac in devices):
                    print(
                        f"\n[NETWORK] "
                        f"{devices[mac]['name']} "
                        f"disconnected"
                    )
                    del devices[mac]
        conn.close()

def main():
    server = socket.socket(socket.AF_INET, socket.SOCK_STREAM)
    server.bind((HOST, PORT))
    server.listen()
    print(
        f"[NETWORK] "
        f"Listening on "
        f"{HOST}:{PORT}"
    )

    while True:
        conn, addr = (server.accept())
        print(
            f"\n[NETWORK] "
            f"Connection "
            f"from {addr}"
        )

        threading.Thread(
            target=
                handle_client,
                args=(conn,),
                daemon=True
        ).start()

if __name__ == "__main__":
    main()