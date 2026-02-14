## Docker

### Budowanie
```bash
docker-compose build
```

### Wyłączanie
```bash
docker-compose --profile mailer down
```

Dodatkowa opcja `-v` usunie dotychczasowe dane.

### Włączanie
```bash
docker-compose --profile mailer up
```
