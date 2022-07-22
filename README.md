# nac-o-meter
Open source website to display LYAC claimed scores

## What is NAC / LYAC?
NAC stands for NRAU ACTIVITY CONTEST and is a flavor of amateur radio activity on VHF and upper bands[1].

## What is NRAU?
NRAU is Nordic Radio Amateur Union, founded back in 1935[2].

## What is amateur radio?
Amateur radio is the greatest hobby ever! An amateur radio operator is someone who uses equipment at an amateur radio 
station to engage in two-way personal communications with other amateur operators on radio frequencies assigned to the 
amateur radio service and without the use of other communications media, such as telephone lines, mobile phones and/or
the internet.

## What is LYAC?
LYAC stands for Lithuanian (LY - ITU prefix) Activity Contest, an amateur radio activity compatible with NRAU[3].

## How does it work?
Radio amateurs make contacts to each other on one of the specific bands and make records of the contacts on the radio log, 
indicating exact date and time, counterparty's call sign, signal strenght and approximate location, expressed in Maidenhead 
coordinate system (i.e. KO24PR). The contact log is usually stored on a computer in a format similar to TSV/CSV.

Once the logs are complete, they are sent to the LYAC email robot which parses the log files, validates and cross-matches
the contacts made between operators and calculates the score by measuring the distance between two given operators and 
issuing one point for each kilometer to both operators. These so-called claimed results[4] are verified by a human judge.

## Why are you doing this?
Because it is a fun way to educate yourself about the science of electromagnetic waves, their propagation, ionosphere and
troposphere features, direct and efficient communication, learn new languages and use of the radio equipment or even building
your own antennae for a particular radio band. 

## About this software

### Requirements

* PHP > 7.4
* MySQL or MariaDB protocol version 10
* Yarn > 3.2

### Deployment

#### Development:

Just run:

```
docker compose up
```

#### Production:

```
APP_ENV=prod composer install --no-dev --optimize-autoloader
yarn encore production
```

Upload the following folders to the web server:

* `vendor/`
* `bin/`
* `config/`
* `public_html/`
* `src/ `
* `templates/`
* `translations/`

## References
* [1] - http://vushf.dk/
* [2] - https://www.nrau.net/about-nrau.html
* [3] - http://www.qrz.lt/lyac/
