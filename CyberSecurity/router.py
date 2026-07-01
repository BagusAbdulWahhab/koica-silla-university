import socket
import threading
import json
import time

from common import (
    send_json
)

HOST = "127.0.0.1"
PORT = 9000

NAME = "Router"
IP = "192.168.1.1"
MAC = "RR"

pending_dns = {}
arp_cache = {}

def receive_loop(sock):
    buffer = ""
    while True:
        try:
            data = sock.recv(1024)
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

                if (not line.strip()):continue

                message = (json.loads(line))

                print(message)

                msg_type = message["type"]
                if (msg_type == "ARP_REQUEST"):
                    target_ip = message["target_ip"]
                    print(
                        f"\n[{NAME}] "
                        f"Received ARP REQUEST for {target_ip} from {message['sender_ip']}"
                    )
                    print(
                        f"Target IP: "
                        f"{target_ip}"
                    )
                    if(target_ip == IP):
                        print(
                            f"Target IP matches router's IP. "
                            f"\nSending ARP REPLY - {IP} -> {MAC}"
                        )
                        send_json(
                            sock,
                            {
                                "type":"UNICAST",
                                "sender_mac":MAC,
                                "destination_mac":message["sender_mac"],
                                "payload":
                                    {
                                        "type":"ARP_REPLY",
                                        "sender_ip":IP,
                                        "sender_mac":MAC,
                                    }
                            }        
                        )
                elif(msg_type == "UNICAST"):
                    payload = message["payload"]
                    if(payload["type"] == "ARP_REPLY"):
                        sender_ip = payload["sender_ip"]
                        sender_mac = payload["sender_mac"]
                        arp_cache[sender_ip] = sender_mac
                        print(f"\n[Router] ARP Cache Update: {sender_ip} -> {sender_mac}")
                    elif(payload["type"] == "DNS_QUERY"):
                        domain = payload["domain"]
                        sender_mac = message["sender_mac"]
                        sender_ip = message.get("sender_ip", None)
                        print(f"\n[Router] Received DNS Query for {domain} from {sender_ip} ({sender_mac})")
                        pending_dns[domain] = (sender_ip, sender_mac)
                        print(f"[Router] Forwarding DNS Query for {domain} to DNS Server")
                        send_json(
                            sock,
                            {
                                "type": "UNICAST",
                                "sender_mac": MAC,
                                "destination_mac": "DD",
                                "payload": {
                                    "type": "DNS_QUERY",
                                    "domain": domain
                                }
                            }
                        )
                    elif(payload["type"] == "DNS_REPLY"):
                        domain = payload["domain"]
                        ip = payload["ip"]
                        print(f"\n[Router] Received DNS Reply for {domain} -> {ip}")
                        if domain in pending_dns:
                            sender_ip, sender_mac = pending_dns[domain]
                            # First try ARP cache (for poisoned entries during spoofing)
                            if sender_ip in arp_cache:
                                destination_mac = arp_cache[sender_ip]
                                print(f"[Router] ARP cache lookup: {sender_ip} -> {destination_mac}")
                            else:
                                # Fallback to stored MAC if not in ARP cache
                                destination_mac = sender_mac
                                print(f"[Router] Using stored MAC: {sender_ip} -> {destination_mac}")
                            
                            print(f"[Router] Forwarding {domain} -> {ip} to {destination_mac}")
                            send_json(
                                sock,
                                {
                                    "type": "UNICAST",
                                    "sender_mac": MAC,
                                    "destination_mac": destination_mac,
                                    "payload": {
                                        "type": "DNS_REPLY",
                                        "domain": domain,
                                        "ip": ip
                                    }
                                }
                            )
                            del pending_dns[domain]    

                elif msg_type == "ARP_REPLAY":
                    sender_ip = message["sender_ip"]
                    sender_mac = message["sender_mac"]
                    arp_cache[sender_ip] = sender_mac
                    print(f"\n[Router] ARP Cache Update")
                    print(f"{sender_ip} -> {sender_mac}")

        except Exception as e:
            print(f"\n[Router ERROR] {e}")
            import traceback
            traceback.print_exc()
            break


def main():

    sock = socket.socket(
        socket.AF_INET,
        socket.SOCK_STREAM
    )

    sock.connect(
        (HOST, PORT)
    )

    send_json(
        sock,
        {
            "type":
                "REGISTER",

            "name":
                NAME,

            "ip":
                IP,

            "mac":
                MAC
        }
    )

    threading.Thread(
        target=
            receive_loop,
        args=(sock,),
        daemon=True
    ).start()
    print(
        "[Router] "
        "Connected"
    )
    print(
    "[Router] "
    "Waiting for packets..."
    )
    while True:
        time.sleep(1)



if __name__ == "__main__":
    main()