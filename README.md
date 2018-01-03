PHP REST API dla SubiektGT + SferaGT
======

API udostępnia interfejs i zapewnia komunikacje z SubiektGT w następujący sposób:

- tworzenie zamówień od klientów
- tworzenie klientów
- tworzenie towarów
- przetworzenie zamówień klientów na fakturę sprzedaży lub paragon imienny
- pobranie dowolnego dokuemtu
- pobranie dowolnego dokumentu w formacie pdf
- pobranie podstawowych danych o kliencie
- pobranie podstawowych danych o towarze oraz jego stanie magazynowym


## Instalacja

aplikacje, które muszą zostać wcześniej zainstalowane na komputerze/serwerze z Windows:
- SubiektGT oraz SferaGT
- serwer WWW (testowano na Apache/2.4.27 (Win32))
- php (testowano na wersji 7.1.9)
- zainstalowane biblioteki php:  com_dotnet, sqlsrv

Pobieramy projekt w wersji master/lub stabilnej lub używamy do tego composer-a.
Poniższy przykład przedstawia pobranie wersji developerskiej.

```
create-project lukeotdr/api-subiekt-gt  --stability dev
```

Paczke umieszczamy tak aby serwer www miał możliwość odczytu plików z katalogu public.
Uruchamiamy konfigurację api poprzez przykładowe poniższe wywyołanie:

```
http://192.168.1.1/api-subiekt-gt/public/setup
```

Powyższe wywyołanie uruchomi konfigurator api, który pomoże utworzyć plik konfiguracyjny do połączenia ze Sferą GT 
oraz SQLServer-em. 

## License

The MIT License (MIT). Please see [License File](https://github.com/LukeOtdr/api-subiekt-gt/blob/devloper/LICENSE) for more information.