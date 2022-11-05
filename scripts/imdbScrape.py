#!/usr/bin/python3
# -*- coding; utf-8 -*-

import json
import ssl
from urllib.request import Request, urlopen
from bs4 import BeautifulSoup

import uploadtoDB
import getStreamable

# For ignoring SSL certificate errors
ctx = ssl.create_default_context()
ctx.check_hostname = False
ctx.verify_mode = ssl.CERT_NONE

# Urls that need to be scraped
top_movies_url = "https://www.imdb.com/chart/top/"
top_tv_shows_url = "https://www.imdb.com/chart/toptv"

# Fetching the BeautifulSoup object of an HTML page by passing its URL


def get_web_page_content(url):
    # Making the website believe that you are accessing it using a mozilla browser
    req = Request(
        url, headers={'User-Agent': 'Mozilla/5.0', 'Accept-Language': 'en-US'})
    webpage = urlopen(req).read()

    # Creating a BeautifulSoup object of the html page for easy extraction of data.
    soup = BeautifulSoup(webpage, 'html.parser')
    return soup


def get_minutes(hour_minutes):
    if hour_minutes.find("h") == -1:
        return hour_minutes.strip("m ")
    elif hour_minutes.find("m") == -1:
        hours = hour_minutes.strip("h ")
        return int(hours) * 60
    else:
        duration = hour_minutes.split()
        hours = duration[0].strip("h ")
        minutes = duration[1].strip("m ")
        return str((int(hours) * 60) + int(minutes))

# Going into individual movie/tv show URL and extracting extra details


def get_extra_details(movie):
    inner_soup = get_web_page_content(movie["imdb_link"])
    movie["poster"] = inner_soup.find(
        'div', attrs={'class': 'ipc-media--poster-27x40'}).find('img').get("src")

    rating = inner_soup.find('div', attrs={'class': 'fAePGh'})
    ratingValue = rating.find('span', attrs={'class': 'jGRxWM'}).text.strip()
    ratingUsers = rating.find('div', attrs={'class': 'dPVcnq'}).text.strip()
    movie["ratings"] = ratingValue + ' based on ' + ratingUsers
    time = inner_soup.find('ul', attrs={'class': 'kqWovI'})
    time = time.findAll('li', attrs={'class': 'ipc-inline-list__item'})
    movie["duration"] = get_minutes(time[-1].text.strip())
    movie["summary"] = inner_soup.find(
        'span', attrs={'class': 'gXUyNh'}).text.strip()

    people = inner_soup.find('div', attrs={'class': 'fjLeDR'})
    Allpeople = people.findAll(
        'li', attrs={'class': 'ipc-metadata-list__item'})
    # movie["director"]
    directors = Allpeople[0].findAll(
        'a', attrs={'class': 'ipc-metadata-list-item__list-content-item'})
    for director in directors:
        if director == directors[0]:
            AllDirectors = director.text.strip()
        else:
            AllDirectors += ", " + director.text.strip()
    movie["director"] = AllDirectors
    # movie["writers"]
    writers = Allpeople[1].findAll(
        'a', attrs={'class': 'ipc-metadata-list-item__list-content-item'})
    for writer in writers:
        if writer == writers[0]:
            AllWriters = writer.text.strip()
        else:
            AllWriters += ", " + writer.text.strip()
    movie["writers"] = AllWriters
    # movie["stars"]
    stars = Allpeople[2].findAll(
        'a', attrs={'class': 'ipc-metadata-list-item__list-content-item'})
    for star in stars:
        if star == stars[0]:
            Allstars = star.text.strip()
        else:
            Allstars += ", " + star.text.strip()
    movie["stars"] = Allstars
    return movie

# Fetching details of n number of movie/tv shows from the IMDB lists


def get_top_rated_imdb_hits(url, file_name, nos):
    print("------" + url + "------")
    soup = get_web_page_content(url)
    divs = soup.find('tbody', attrs={'class': 'lister-list'})
    movies = []

    i = 1
    for tr in divs.findAll('tr'):
        movie = {}
        td = tr.find('td', attrs={'class': 'titleColumn'})
        a = td.find('a')
        ref = a["href"].split("/")
        movie["id"] = ref[2][2:]
        ref = "/" + ref[1] + "/" + ref[2]
        movie["imdb_link"] = "https://www.imdb.com" + ref + "/"
        movie_data = td.text.strip().split("\n")
        movie["rank"], movie["name"], movie["year"] = movie_data[0].strip(
            '.'), movie_data[1].strip(), movie_data[2].strip()[1:5]
        print(movie["rank"], movie["name"])
        movie = get_extra_details(movie)
        movies.append(movie)
        i += 1
        if i > nos:
            break

    # Creates a json file with all the information that you extracted
    with open(file_name, 'w') as outfile:
        json.dump(movies, outfile, indent=4)

    print("----------- scraped movie list -----------")
    print("-------- uploading new list to db --------")
    uploadtoDB.upload_to_db(movies)

    print("-------- uploading new streamables --------")
    getStreamable.uploadNewStreamables()


get_top_rated_imdb_hits(top_movies_url, 'movies.json', 250)
#get_top_rated_imdb_hits(top_tv_shows_url, 'tv_shows.json', 250)
print('--------- Extraction of data is complete. Check JSON file. ---------')
