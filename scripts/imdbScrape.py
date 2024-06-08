#!/usr/bin/python3
# -*- coding; utf-8 -*-

import json
import ssl
from urllib.request import Request, urlopen
from bs4 import BeautifulSoup
import time

import uploadtoDB

# For ignoring SSL certificate errors
ctx = ssl.create_default_context()
ctx.check_hostname = False
ctx.verify_mode = ssl.CERT_NONE

# Urls that need to be scraped
top_movies_url = "https://www.imdb.com/chart/top/"
top_tv_shows_url = "https://www.imdb.com/chart/toptv"


def get_web_page_content(url):
    req = Request(
        url, headers={'User-Agent': 'Mozilla/5.0', 'Accept-Language': 'en-US'})
    webpage = urlopen(req).read()

    return BeautifulSoup(webpage, 'html.parser')


def get_poster(soup):
    return soup.find('div', attrs={'class': 'ipc-media--poster-27x40'}).find('img').get("src")


def get_ratings(soup):
    rating = soup.find('div', attrs={'class': 'sc-bde20123-0'})
    ratingValue = rating.find(
        'span', attrs={'class': 'sc-bde20123-1'}).text.strip()
    ratingUsers = rating.find(
        'div', attrs={'class': 'sc-bde20123-3'}).text.strip()
    return ratingValue + ' based on ' + ratingUsers


def get_summary(soup):
    return soup.find('span', attrs={'class': 'sc-eb5317c9-2'}).text.strip()


def get_people(soup):
    artist_list = soup.find('ul', attrs={'class': 'title-pc-list'})
    return artist_list.findAll(recursive=False)


def find_in_list(list):
    return list.findAll('a', attrs={'class': 'ipc-metadata-list-item__list-content-item'})


def get_names(people):
    names = find_in_list(people)
    for name in names:
        if name == names[0]:
            all_names = name.text.strip()
        else:
            all_names += ", " + name.text.strip()
    return all_names


def get_extra_details(movie):
    soup = get_web_page_content(movie["imdb_link"])

    movie["poster"] = get_poster(soup)
    movie["ratings"] = get_ratings(soup)
    movie["summary"] = get_summary(soup)

    all_people = get_people(soup)

    movie["director"] = get_names(all_people[0])
    movie["writers"] = get_names(all_people[1])
    movie["stars"] = get_names(all_people[2])

    return movie


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


def get_duration(soup):
    metadata = soup.find('div', attrs={'class': 'cli-title-metadata'})
    return get_minutes(metadata.findAll('span')[1].text.strip())


def get_release_year(soup):
    metadata = soup.find('div', attrs={'class': 'cli-title-metadata'})
    return metadata.findAll('span')[0].text.strip()


def get_title(soup):
    a = soup.find('a')
    movie_data = a.find('h3').text.split()
    movie_data.pop(0)
    return ' '.join(movie_data)


def get_rank(soup):
    a = soup.find('a')
    movie_data = a.find('h3').text.split()
    return movie_data[0].strip('.')


def get_link(movie):
    return "https://www.imdb.com/title/tt" + movie["id"] + "/"


def get_movie_id(soup):
    a = soup.find('a')
    ref = a["href"].split("/")
    return ref[2][2:]


def get_top_rated_imdb_hits(url, file_name):
    print("------" + url + "------")
    soup = get_web_page_content(url)
    movie_list = soup.find('ul', attrs={'class': 'compact-list-view'})
    movies = []

    for item in movie_list.findAll('li', attrs={'class': 'ipc-metadata-list-summary-item'}):
        movie = {}
        item_data = item.find('div', attrs={'class': "cli-children"})

        movie["id"] = get_movie_id(item_data)
        movie["imdb_link"] = get_link(movie)
        movie["rank"] = get_rank(item_data)
        movie["name"] = get_title(item_data)
        movie["year"] = get_release_year(item_data)
        movie["duration"] = get_duration(item_data)

        print(movie["rank"], movie["name"])

        movie = get_extra_details(movie)
        movies.append(movie)

    # Creates a json file with all the information that you extracted
    with open(file_name, 'w') as outfile:
        json.dump(movies, outfile, indent=4)

    print("----------- scraped movie list -----------")
    print("-------- uploading new list to db --------")
    uploadtoDB.upload_to_db(movies)

    print("-------- uploading new streamables --------")
    # getStreamable.uploadNewStreamables()


get_top_rated_imdb_hits(top_movies_url, 'movies.json')
# get_top_rated_imdb_hits(top_tv_shows_url, 'tv_shows.json')
print('--------- Extraction of data is complete. Check JSON file. ---------')
