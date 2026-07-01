import socket
import threading
import json
import time

from common import send_json

HOST = "127.0.0.1"
PORT = 9000

NAME = "DNS Server"
IP = "8.8.8.8"
MAC = "DD"

records = {
    "bank.com": "93.184.216.34",
    "facebook.com": "157.240.22.35",
    "youtube.com": "142.250.199.46"
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
                if not line.strip():
                    continue
                message = json.loads(line)

                if (message["type"]== "UNICAST"):
                    payload = message["payload"]
                    if (payload["type"]== "DNS_QUERY"):
                        domain = payload["domain"]
                        print(
                            f"\n[DNS] "
                            f"Query: "
                            f"{domain}"
                        )

                        if (domain in records):
                            ip = records[domain]

                            print(
                                f"[DNS] "
                                f"Reply: "
                                f"{domain} "
                                f"-> "
                                f"{ip}"
                            )
                            send_json(sock,
                                {
                                    "type":"UNICAST",
                                    "sender_mac":MAC,
                                    "destination_mac":message["sender_mac"],
                                    "payload":
                                    {
                                        "type":"DNS_REPLY",
                                        "domain":domain,
                                        "ip":ip
                                    }
                                }
                            )

        except Exception as e:
            print(
                f"\n[DNS ERROR] "
                f"{e}"
            )

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
        "[DNS] Connected"
    )

    while True:
        time.sleep(1)

if __name__ == "__main__":
    main()