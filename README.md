PHP REST API dla SubiektGT + SferaGT
======

API udostępnia interfejs i zapewnia komunikacje z SubiektGT w następujący sposób:

- tworzenie zamówień od klientów
- tworzenie klientów
- tworzenie towarów
- przetworzenie zamówień klientów na fakturę sprzedaży lub paragon imienny
- pobranie dowolnego dokumentu
- pobranie dowolnego dokumentu w formacie pdf
- pobranie podstawowych danych o kliencie
- pobranie podstawowych danych o towarze oraz jego stanie magazynowym

## Wymagania

Aplikacje, które muszą zostać wcześniej zainstalowane na komputerze/serwerze z Windows:
- SubiektGT oraz SferaGT (testowano na 1.50 HF1)
- serwer WWW (testowano na Apache/2.4.27 (Win32))
- php (testowano na wersji 7.1.9)
- zainstalowane biblioteki php:  com_dotnet, sqlsrv

## Instalacja

Pobieramy projekt w wersji master/lub stabilnej lub używamy do tego composer-a.
Poniższy przykład przedstawia pobranie wersji developerskiej.

```
create-project lukeotdr/api-subiekt-gt  --stability dev
```

Paczkę umieszczamy tak aby serwer www miał możliwość uruchomienia plików PHP z katalogu public lub odpowiednio konfigurujemy serwer www.
Uruchamiamy konfigurację api poprzez przykładowe poniższe wywołanie:

```
http://192.168.1.1/api-subiekt-gt/public/setup
```

Powyższe wywołanie uruchomi konfigurator api, który pomoże utworzyć plik konfiguracyjny do połączenia ze Sferą GT 
oraz SQLServer-em.  Należy przygotować użytkownika oraz hasło do SQLServera dzięki któremu zostanie nawiązane połączenie z 
bazą Subiekta. Jeśli była użyta autentykacja windows trzeba utworzyć użytkownika z dostępem do podmiotu. 
Do testów można użyć danych admina "sa" lecz na produkcji nie zalecane. 

Po konfiguracji należy jeszcze przeprowadzić test połączenia podając istniejący numer dokumentu sprzedaży z Subiekta. Np: "PA 13659/12/2017".
W odpowiedzi i poprawnego połączenia powinniśmy zobaczyć coś podobnego jak poniżej.

Wysłane rządanie:

```
192.168.1.1/api-subiekt-gt/public/api/document/get
{
    "api_key": "XXXXXXXXXXXXXXXXXXXXXX",
    "data": {
        "doc_ref": "PA 13659/12/2017"
    }
}
```

XXXXXXXXXXXXXXXXXXXXXX - wygenerowane api key.

Odebrana odpowiedź:

```
{
    "state": "success",
    "data": {
        "products": [
            {
                "name": "MUND Skarpety PAMIR r.M czarny",
                "code": "8424752732026",
                "qty": "1.0000",
                "price": "34.9200"
            },
            {
                "name": "ARC'TERYX Woreczek APERTURE CHALK BAG large fiolet",
                "code": "806955782905",
                "qty": "1.0000",
                "price": "62.5700"
            },
            {
                "name": "ARC'TERYX Plecak CIERZO 18 dk basalt",
                "code": "806955927375",
                "qty": "1.0000",
                "price": "175.5200"
            },
            {
                "name": "LIFESYSTEMS Gwizdek ratunkowy SAFETY WHISTLE",
                "code": "5031863022507",
                "qty": "1.0000",
                "price": "27.0600"
            }
        ],
        "fiscal_state": 1,
        "accounting_state": 0,
        "reference": "",
        "comments": "",
        "customer": [],
        "doc_ref": "PA 13659/12/2017",
        "doc_type": "PA",
        "amount": "300.0700",
        "state": 1,
        "date_of_delivery": null,
        "is_exists": true,
        "gt_id": 171417
    }
}
```


Jeśli udało się połączyć z bazą danych to teraz nie pozostaje nic innego jak utworzyć interfejs do komunikacji z api.
Rządania do api w powyższym przykładzie wysyłamy na adres:

```
http://192.168.1.1/api-subiekt-gt/public/api/

przykładowe wywołanie:

http://192.168.1.1/api-subiekt-gt/public/api/document/get

```


**UWAGA** - adres "setup-u" należy zabezpieczyć przed nieautoryzowanym dostępem np przez .htaccess dyrektywy allow deny. Można również przkopiować katalog w inne miejsce. 

Gdyby zaszła potrzeba użyć IIS-a jako serwera www to rządania bez modułu "rewrite" miałyby postać:

```
http://192.168.1.1/api-subiekt-gt/public/api?c=document/get
```

## Dokumentacja API 

 Dokumentacja metod API: [Dokumentacja](https://github.com/LukeOtdr/api-subiekt-gt/wiki)

## License

The MIT License (MIT). Please see [License File](https://github.com/LukeOtdr/api-subiekt-gt/blob/devloper/LICENSE) for more information.