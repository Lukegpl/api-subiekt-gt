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

aplikacje które muszą się zostać wcześniej zainstalowane na maszymie Windows dalej nazywanym serwerem:
- SubiektGT oraz SferaGT
- serwer WWW apache
- php 


```
composer require league/plates
```

## Documentation

Full documentation can be found at [platesphp.com](http://platesphp.com/).

## Testing

```bash
phpunit
```

## Contributing

Please see [CONTRIBUTING](https://github.com/thephpleague/plates/blob/master/CONTRIBUTING.md) for details.

## Security

If you discover any security related issues, please email rj@bighead.net instead of using the issue tracker.

## Credits

- [RJ Garcia](https://github.com/ragboyjr) (Current Maintainer)
- [Jonathan Reinink](https://github.com/reinink) (Original Author)
- [All Contributors](https://github.com/thephpleague/plates/contributors)

## License

The MIT License (MIT). Please see [License File](https://github.com/thephpleague/plates/blob/master/LICENSE) for more information.