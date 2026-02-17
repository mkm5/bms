# Docker

## Produkcyjny / Prezentacyjny

### Budowanie
```bash
docker compose build
```

### Załączanie
```bash
docker compose --profile mailer up
```

Flaga `--profile mailer` uruchomi program __mailpit__, który służy do przechwytywania e-maili. Interfejs webowy powinien być dostępny pod adresem __http://localhost:8025__. Program ten jest potrzebny ze względnu na proces rejestracji oraz logowania przy pomocy linku. Przy pierwszym załączeniu powinno powsać konto administracyjne do którego można zalogować się przy pomocy:
* **login**: admin@localhost
* **hasło**: admin

### Wyłączanie
```bash
docker compose --profile mailer down
```

Dodatkowa opcja `-v` usunie dotychczasowe dane.

