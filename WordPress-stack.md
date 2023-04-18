# Software stack for WordPress

:bulb: Essentially keep every operation in memory!

- Modern CPU, high *memory* bandwidth as WordPress is mainly memory copying,
  sub-msec disk access time, use [UpCloud](https://www.upcloud.com/register/?promo=U29Q8S)
- Thin virtualization layer, use UpCloud, stay away from popular, not enterprise ready providers
- Fast operating system: No systemd, Enough entropy, IRQ balance, Low memory usage
- Block hammering attackers: Fail2ban, permanently block hostile networks
- Anycast DNS
- Quick webserver: Apache with Event MPM
- Parallel connection CDN with RAM-like speeds (Amazon CloudFront)
- [High speed SSL](https://istlsfastyet.com/): ECDSA certificate, Entropy source,
  TLS1.2, Ciphersuites for AES-NI, SSL session cache, OCSP stapling, HTTP/2
- Modern PHP with OPcache, FastCGI connected through UDS
- Lean WordPress installation: minimal and audited plugins only
- Redis in-memory object cache
- InnoDB MariaDB engine
- Static resource optimization
- Cut on JavaScripts and stylesheets
- Continuous monitoring (Monit, HetrixTools, Bugsnag)

###### This page contains an affiliate link
