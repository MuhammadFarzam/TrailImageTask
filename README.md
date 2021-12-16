## Install Ocr Package on your System through this command

sudo apt install tesseract-ocr -y

## Install Dependencies of Project which install Ocr dependencies , mongo db and others

Composer install

## Generate env File from env Example  and generate key

- cp .env.example .env
- php artisan key:generate
- php artisan migrate

## Project Dependencies

- Install mondo server on your local system
- Php version >= 7.4

## Project Breakdown

- Limit to scan two types of Invoices 
    - Daraz
    - General custom create

Use Ajax to Upload Images and it will return response which images upload or render correctly 

- Create api for listing 
Endpoint : localhost/invoices/


- Filter Api to get specific record on the basis of id and invoice no 
Endpoint : localhost/invoices/{identifier} // identifies is id or invoice no