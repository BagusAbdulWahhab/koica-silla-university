import time
import socket
import threading
import json

from common import (
    send_json
)

HOST = "127.0.0.1"
PORT = 9000

NAME = "Alice"
IP = "192.168.1.10"
MAC = "AA"

arp_cache = {}
dns_cache = {}
current_domain = None
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
                line, buffer = (buffer.split(
                        "\n",
                        1
                    )
                )

                if (not line.strip()):
                    continue

                message = (json.loads(line))

                print(
                    f"\n[{NAME}] "
                    f"Received:"
                )

                msg_type = message["type"]
                if(msg_type == "UNICAST"):
                    payload =message["payload"]
                    if payload["type"] == "ARP_REPLY":
                        sender_ip = (payload["sender_ip"])
                        sender_mac = (payload["sender_mac"])
                        arp_cache[sender_ip] = sender_mac
                        print(
                            f"\n[{NAME}] "
                            "Received ARP Reply"
                        )
                        print(
                            f"Sender IP: "
                            f"{sender_ip} is at -> {sender_mac}"
                        )
                    elif payload["type"] == "DNS_REPLY":
                        domain = payload["domain"]
                        ip = payload["ip"]
                        dns_cache[domain] = ip
                        print(
                            f"\n[{NAME}] "
                            "Received DNS Reply"
                        )
                        web_server_ip = dns_cache[domain]
                        print(
                            f"{domain} -> {ip}"
                        )
                        print("\nStarting TCP Handshake...")
                        print("\nTCP Connection Established.")
                        print("\n\nStarting TLS Handshake...")
                        send_json(
                            sock,
                            {
                                "type": "UNICAST",
                                "sender_mac": MAC,
                                "destination_mac": "WS",
                                "payload": {
                                    "type": "TLS_CLIENT_HELLO",
                                    "domain": domain
                                }
                            }
                        )
                    elif(payload["type"] == "TLS_SERVER_HELLO"):
                        certificate = payload["certificate"]
                        common_name = (certificate["common_name"])
                        issuer = (certificate["issuer"])

                        print("\nTLS Server Hello Received")
                        #print(
                        #    f"Certificate CN: "
                        #    f"{common_name}"
                        #    )
                        #print(
                        #    f"issuer: "
                        #    f"{issuer}"
                        #)
                        if(common_name == current_domain):
                            print("\nHTTPS Connection Established")
                        else:
                            print("\nCertificate Missmatch")
                elif msg_type == "ARP_REPLY":
                    sender_ip = message["sender_ip"]
                    sender_mac = message["sender_mac"]
                    arp_cache[sender_ip] = sender_mac
                    print("\n[Alice] ARP Cache Update")
                    print(f"{sender_ip} -> {sender_mac}")

        except:
            break


def main():
    global current_domain
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
        target=
            receive_loop,
            args=(sock,),
            daemon=True
    ).start()

    print(
        "[Alice] "
        "Connected"
    )

    while True:
        print(
            "\n1. Open Website"
            "\n2. Show ARP Cache"
            "\n3. Show DNS Cache"
            "\n0. Exit"
        )

        cmd = input(">> ")

        if cmd == "1":
            domain = input("Domain: ")
            current_domain = domain
            print(
                f"\n[{NAME}] "
                f"Requested to open "
                f"{domain}"
            )
            gateway_ip = ("192.168.1.1")
            if(gateway_ip not in arp_cache):
                print("\nARP Cache miss")
                print("Sending ARP Request")
                send_json(
                    sock,
                    {
                        "type":"ARP_REQUEST",
                        "sender_mac": MAC,
                        "sender_ip": IP,
                        "target_ip": gateway_ip
                    }
                )
                time.sleep(2)  # Wait for ARP reply
            
            # After ARP resolution, send DNS query
            if(gateway_ip in arp_cache):
                print("\nARP Cache hit")
                print(f"{gateway_ip} -> {arp_cache[gateway_ip]}")
                if (domain not in dns_cache):
                    print("\nDNS Cache miss")
                    print(f"Sending DNS Query Request for {domain}")
                    router_mac = arp_cache[gateway_ip]
                    send_json(
                        sock,
                        {
                            "type":"UNICAST",
                            "sender_mac":MAC,
                            "sender_ip":IP,
                            "destination_mac":router_mac,
                            "payload":
                            {
                                "type":"DNS_QUERY",
                                "domain":domain
                            }
                        }
                    )
                    time.sleep(2)  # Wait for DNS reply
                else:
                    print("\nDNS Cache hit")
                    print(f"{domain} -> {dns_cache[domain]}")

        elif cmd == "2":
            print("\nARP Cache:")
            if not arp_cache:
                print("Empty")
            else:
                for ip, mac in (arp_cache.items()):
                    print(
                        f"{ip}"
                        f"->" 
                        f"{mac}"
                    )    

        elif cmd == "3":
            print("\nDNS CACHE:")
            if not dns_cache:
                print("Empty")
            else:
                for domain, ip in (dns_cache.items()):
                    print(
                        f"{domain}"
                        f"->" 
                        f"{ip}"
                    )
        elif cmd == "0":
            break
if __name__ == "__main__":
    main()