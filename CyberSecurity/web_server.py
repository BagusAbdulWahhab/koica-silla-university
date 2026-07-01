import socket
import threading
import json
import time

from common import send_json

HOST = "127.0.0.1"
PORT = 9000

NAME = "Web Server"
IP = "93.184.216.34"
MAC = "WS"

def get_certificate(domain):
    return {
        "common_name": domain,
        "issuer": "Trusted CA"
    }

def receive_loop(sock):
    buffer = ""
    while True:
        try:
            data = sock.recv(1024)
            if not data:
                break

            buffer += data.decode()
            while "\n" in buffer:
                line, buffer = buffer.split(
                    "\n",
                    1
                )
                if not line.strip():continue

                message = json.loads(line)
                if (message["type"]== "UNICAST"):
                    payload = message["payload"]
                    if (payload["type"]== "TLS_CLIENT_HELLO"):
                        domain = payload["domain"]
                        print(f"\n[WEB SERVER]")
                        print(f"TLS Client Hello")
                        print(f"Requested Domain: {domain}")
                        certificate = get_certificate(domain)
                        send_json(
                            sock,
                            {
                                "type":"UNICAST",
                                "sender_mac":MAC,
                                "destination_mac":message["sender_mac"],
                                "payload":
                                {
                                    "type":"TLS_SERVER_HELLO",
                                    "certificate":certificate
                                }
                            }
                        )

        except Exception as e:
            print(f"\n[WEB SERVER ERROR]")
            print(e)
            break

def main():
    sock = socket.socket(
        socket.AF_INET,
        socket.SOCK_STREAM
    )
    sock.connect((HOST, PORT))

    send_json(
        sock,
        {
            "type":"REGISTER",
            "name":NAME,
            "ip":IP,
            "mac":MAC
        }
    )

    threading.Thread(
        target=receive_loop,
        args=(sock,),
        daemon=True
    ).start()

    print(
        "[Web Server] Connected"
    )

    while True:
        time.sleep(1)

if __name__ == "__main__":
    main()