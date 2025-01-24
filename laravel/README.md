# Gra Miejska
Aplikacja internetowa “Gra Miejska” wykonana na PHP Frameworku Laravel, posiada odseparowany front i back-end, a komunikacja idzie poprzez POST API.

# Uruchomienie
Aplikacja jest skonteneryzowana. Do uruchomienia przejdź do ścieżki aplikacji w terminalu Dockera i wpisz:
```
docker compose up -d
```

# Działanie
 Aplikacja raz dziennie o tej samej godzinie losowo wybiera każdemu użytkownikowi jedno ze zdjęć wykonanych przez administratora. Użytkownik z pomocą zdjęcia próbuje odgadnąć gdzie zostało wykonane, potwierdzając swój wybór klikając miejsce na mapie geograficznej. Bazując na odległości od celu, czasu użytego na podjęcie wyboru oraz trudności zdjęcia przyznawane są punkty. Użytkownicy konkurują ze sobą ilością punktów na tablicy wyników.
Założenia:

a) Użytkownik może:
- Zarejestrować oraz zalogować się w systemie - poprzez bazę danych, Google lub Facebooka.
- Zmienić swoje zdjęcie profilowe.
- Raz dziennie o wyznaczonej godzinie otrzymać losowe zdjęcie z systemu, jego zadaniem jest odgadnięcie gdzie zdjęcie zostało wykonane wskazując miejsce na mapie.
- Bazując na wykonanym wyborze otrzymać punkty.
- Zobaczyć tablicę wyników innych zalogowanych użytkowników wraz z podium.

b) Administrator może:
- Dodawać nowe zdjęcia do bazy zdjęć.
- Edytować opis oraz koordynaty każdego z dodanych zdjęć.
- Zmienić wartość o której godzinie zagadka się resetuje.
- Zmienić wartość dopuszczalnego maksymalnego dystansu na mapie aby otrzymać jakiekolwiek punkty.
- Zmienić wartość z ilu dni tablica wyników zlicza punkty.
- Zobaczyć statystyki kliknięć pod każdą zagadką, takie jak średnia liczba uzyskanych punktów, średni czas wykonania wyboru oraz średnia odległość od celu.
- Zobaczyć ilu użytkowników odpowiedziało na ile zagadek.
- Zarządzać użytkownikami.
