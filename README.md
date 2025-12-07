# Apartmány pod Roháčmi - Vaiicko Framework

Webová aplikácia pre správu ubytovaní a atrakcií v oblasti Roháčov, vytvorená pomocou [Vaiicko MVC frameworku](https://github.com/thevajko/vaiicko).

## 📋 Obsah
- [Požiadavky](#požiadavky)
- [Inštalácia](#inštalácia)
- [Spustenie projektu](#spustenie-projektu)
- [Štruktúra projektu](#štruktúra-projektu)
- [Databáza](#databáza)
- [Funkcie aplikácie](#funkcie-aplikácie)
- [Testovacie účty](#testovacie-účty)

## 🔧 Požiadavky

- **Docker Desktop** (pre Windows/Mac) alebo **Docker Engine** (pre Linux)
- **Docker Compose**
- Git (voliteľné, pre klonovanie)

## 📥 Inštalácia

### 1. Klonovanie repozitára

```bash
git clone <url-repozitára>
cd apartmany-rohace-vaiicko
```

### 2. Štruktúra projektu

```
apartmany-rohace-vaiicko/
├── App/
│   ├── Configuration.php      # Konfigurácia aplikácie
│   ├── init.sql               # MySQL databázový script
│   ├── Controllers/           # Kontroléry aplikácie
│   │   ├── AccommodationController.php
│   │   ├── AttractionController.php
│   │   ├── AuthController.php
│   │   └── HomeController.php
│   ├── Models/                # Modely
│   │   ├── User.php
│   │   ├── Accommodation.php
│   │   ├── Attraction.php
│   │   ├── Reservation.php
│   │   └── Review.php
│   └── Views/                 # Views (budú doplnené)
├── Framework/                 # Jadro frameworku (nemeniť)
├── public/                    # Verejný adresár
│   ├── css/
│   │   └── style.css
│   ├── js/
│   │   └── app.js
│   └── index.php
├── docker/                    # Docker konfigurácia
│   ├── .env                   # Databázová konfigurácia
│   └── docker-compose.yml
└── README.md
```

## 🚀 Spustenie projektu

### Použitie Docker

1. **Otvorte terminál v koreňovom adresári projektu**

2. **Spustite Docker kontajnery:**

```bash
cd docker
docker-compose up -d
```

3. **Importujte databázu:**

Po prvom spustení musíte naimportovať databázovú schému.

**Cez Adminer (jednoduchšie):**
- Otvorte [http://localhost:8080](http://localhost:8080)
- Prihláste sa:
  - Server: `db`
  - User: `apartmany_user`
  - Password: `admin`
  - Database: `apartmany_rohace`
- Kliknite na "SQL command"
- Skopírujte obsah súboru `App/init.sql`
- Spustite (Execute)

**Cez príkazový riadok:**

```bash
# Windows PowerShell
Get-Content ..\App\init.sql | docker exec -i apartmany_rohace-db-1 mysql -uapartmany_user -padmin apartmany_rohace

# Linux/Mac
cat ../App/init.sql | docker exec -i apartmany_rohace-db-1 mysql -uapartmany_user -padmin apartmany_rohace
```

4. **Otvorte aplikáciu v prehliadači:**

```
http://localhost
```

### Zastavenie servera

```bash
docker-compose down
```

### Úplné vyčistenie (vrátane databázy)

```bash
docker-compose down -v
```

## 🗄️ Databáza

### Konfigurácia

Databázová konfigurácia je v súbore `docker/.env`:

```env
MARIADB_ROOT_PASSWORD=admin
MARIADB_DATABASE=apartmany_rohace
MARIADB_USER=apartmany_user
MARIADB_PASSWORD=admin
```

### Tabuľky

- **users** - Používatelia (turisti, ubytovatelia, admin)
- **accommodation** - Ubytovania
- **attraction** - Atrakcie
- **reservation** - Rezervácie
- **review** - Hodnotenia
- **accommodation_attraction** - Prepojovacia tabuľka

### Adminer

Databázový nástroj dostupný na: [http://localhost:8080](http://localhost:8080)

## ✨ Funkcie aplikácie

### Verejné funkcie
- ✅ Prezeranie ubytovaní a atrakcií
- ✅ Vyhľadávanie a filtrovanie ubytovaní
- ✅ Detail ubytovania s atrakciami v okolí
- ✅ Registrácia a prihlásenie

### Funkcie po prihlásení
- ✅ Pridávanie, editácia a mazanie ubytovaní (ubytovateľ)
- ✅ Vytváranie rezervácií
- ✅ Hodnotenie ubytovaní
- ✅ Správa vlastných ubytovaní

### Admin funkcie
- ✅ Správa všetkých ubytovaní
- ✅ Správa atrakcií (CRUD operácie)
- ✅ Správa používateľov
- ✅ Prehľad všetkých rezervácií

## 👤 Testovacie účty

Po importe databázy budú k dispozícii tieto účty:

| Email | Heslo | Rola | Popis |
|-------|-------|------|-------|
| admin@apartmany.sk | password | admin | Administrátor systému |
| ubytovatel@test.sk | password | ubytovatel | Ubytovateľ s existujúcimi ubytovaniami |
| turista@test.sk | password | turista | Bežný používateľ |

## 🛠️ Vývoj

### Pridanie nového kontroléra

1. Vytvorte súbor v `App/Controllers/`
2. Zdedite z `Framework\Core\BaseController`
3. Implementujte metódy

```php
<?php
namespace App\Controllers;

use Framework\Core\BaseController;
use Framework\Http\Request;
use Framework\Http\Responses\Response;

class MyController extends BaseController
{
    public function index(Request $request): Response
    {
        return $this->html(['data' => 'value']);
    }
}
```

### Pridanie nového modelu

1. Vytvorte súbor v `App/Models/`
2. Zdedite z `App\Core\Model`

```php
<?php
namespace App\Models;

use App\Core\Model;

class MyModel extends Model
{
    protected ?int $id = null;
    protected ?string $name = null;
    
    // Framework automaticky mapuje na databázovú tabuľku
}
```

### Routing

Framework automaticky mapuje URL na kontroléry:
- `?c=accommodation&a=index` → `AccommodationController::index()`
- `?c=auth&a=login` → `AuthController::login()`

## 📚 Dokumentácia frameworku

Pre viac informácií o frameworku Vaiicko navštívte:
- [GitHub](https://github.com/thevajko/vaiicko)
- [Wiki](https://github.com/thevajko/vaiicko/wiki) (slovensky)

## 🐛 Riešenie problémov

### Docker kontajnery sa nespustia

```bash
# Skontrolujte či beží Docker Desktop
# Zrušte staré kontajnery
docker-compose down -v
docker-compose up -d
```

### Chyba pri pripojení k databáze

1. Skontrolujte či beží databázový kontajner: `docker ps`
2. Overte správnosť údajov v `docker/.env` a `App/Configuration.php`
3. Reštartujte kontajnery

### Prázdna stránka / 500 Error

1. Skontrolujte logy: `docker-compose logs web`
2. Overte že databáza bola naimportovaná
3. Skontrolujte oprávnenia k súborom

## 📝 Licencia

Tento projekt je vytvorený pre vzdelávacie účely v rámci predmetu VAII na FRI UNIZA.

## 👨‍💻 Autor

Marek Kurčin
- Projekt: Apartmány pod Roháčmi
- Framework: [Vaiicko](https://github.com/thevajko/vaiicko)

---

**Poznámka:** Tento projekt využíva Vaiicko MVC framework, ktorý bol vytvorený na podporu výučby predmetu VAII na Fakulte riadenia a informatiky Žilinskej univerzity.

