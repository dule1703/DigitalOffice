# DigitalOffice

# Aplikacija za izradu ponuda vozila

Aplikacija je predviđena za auto-kuće kao pomoćno sredstvo klijentima da mogu da dobiju predlog ponude za koju su zainteresovani.

## Registracija i prijava

- Potrebno je prvo da se radnik registruje. Za registraciju se koriste ime i prezime, email i lozinka.
- Nakon uspešne registracije, korisnik se prijavljuje email-om i lozinkom.
- Implementirana je 2FA autentikacija – korisnik unosi 6-cifreni kod koji dobija na email.
- Dostupna je opcija za promenu lozinke u slučaju zaboravljene šifre.

## Navigacija

- Nakon prijave otvara se Home (Dashboard) stranica sa opcijama **Klijent** i **Ponuda**.
- Prvo se unosi klijent za kog se izrađuje ponuda (kreiranje, izmena, brisanje).
- Zatim se prelazi na stranicu **Ponude** sa prikazom svih ponuda u tabeli (pretraga po više kriterijuma).
- Iznad tabele nalazi se dugme za kreiranje nove ponude.

## Konfigurator modela

- Omogućava izbor željenih modela, paketa i dodatne opreme.
- Izračunava se cena sa i bez PDV-a za svaki model posebno.
- Da bi se zaključila ponuda, mora biti snimljen bar jedan model.

## Upravljanje ponudama

- Nakon snimanja, korisnik se vraća na tabelarni prikaz ponuda.
- Svaka ponuda se može izmeniti, obrisati ili odštampati.




# Vehicle Offer Management App

This application is designed for car dealerships as a tool to help clients receive customized offer proposals based on their preferences.

## Registration and Login

- An employee must first register by entering their full name, email, and password.
- After successful registration, the user logs in using their email and password.
- Two-Factor Authentication (2FA) is implemented – the user must enter a 6-digit code received via email.
- A password reset feature is available if the user forgets their password.

## Navigation

- After login, the user lands on the Home (Dashboard) page with two main options: **Client** and **Offer**.
- First, a client is created (with options to create, edit, and delete).
- Then, the user navigates to the **Offers** page which displays all offers in a searchable table.
- A button above the table allows creating a new offer.

## Model Configurator

- Allows selecting car models, packages, and additional equipment.
- Calculates price with and without VAT per model.
- At least one model must be saved before an offer can be finalized.

## Offer Management

- After saving, the user is returned to the offers table view.
- Each offer can be edited, deleted, or printed.


