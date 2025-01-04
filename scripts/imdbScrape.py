#!/usr/bin/python3
# -*- coding; utf-8 -*-

import json
from bs4 import BeautifulSoup
from selenium import webdriver
import time

import uploadtoDB

# Urls that need to be scraped
top_movies_url = "https://www.imdb.com/chart/top/"
top_tv_shows_url = "https://www.imdb.com/chart/toptv"

def get_web_page_content(url, wait):
    options = webdriver.FirefoxOptions()
    driver = webdriver.Firefox(options=options)
    driver.get(url=url)
    if wait:
        driver.implicitly_wait(10)
        time.sleep(2)
    page = driver.page_source
    driver.close()

    return BeautifulSoup(page, 'html.parser')


def get_poster(soup):
    return soup.find('div', attrs={'class': 'ipc-media--poster-27x40'}).find('img').get("src")


def get_ratings(soup):
    rating = soup.find('div', attrs={'class': 'sc-d541859f-0'})
    ratingValue = rating.find(
        'span', attrs={'class': 'sc-d541859f-1'}).text.strip()
    ratingUsers = rating.find(
        'div', attrs={'class': 'sc-d541859f-3'}).text.strip()
    return ratingValue + ' based on ' + ratingUsers


def get_summary(soup):
    return soup.find('span', attrs={'class': 'sc-3ac15c8d-1'}).text.strip()


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
    soup = get_web_page_content(movie["imdb_link"], False)

    movie["poster"] = get_poster(soup)

    movie["ratings"] = get_ratings(soup)
    movie["summary"] = get_summary(soup)

    all_people = get_people(soup)

    if len(all_people) >= 1:
        movie["director"] = get_names(all_people[0])
    else:
        movie["director"] = ""
    if len(all_people) >= 2:
        movie["writers"] = get_names(all_people[1])
    else:
        movie["writers"] = ""
    if len(all_people) >= 3:
        movie["stars"] = get_names(all_people[2])
    else:
        movie["stars"] = ""

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

def are_valid_shawshank_details(movie):
    expected_movie = {
        "id": "0111161",
        "imdb_link": "https://www.imdb.com/title/tt0111161/",
        "rank": "1",
        "name": "The Shawshank Redemption",
        "year": "1994",
        "duration": "142",
        "poster": "https://m.media-amazon.com/images/M/MV5BMDAyY2FhYjctNDc5OS00MDNlLThiMGUtY2UxYWVkNGY2ZjljXkEyXkFqcGc@._V1_QL75_UX190_CR0,2,190,281_.jpg",
        "ratings": "9.3 based on 3M",
        "summary": "A banker convicted of uxoricide forms a friendship over a quarter century with a hardened convict, while maintaining his innocence and trying to remain hopeful through simple compassion.",
        "director": "Frank Darabont",
        "writers": "Stephen King, Frank Darabont",
        "stars": "Tim Robbins, Morgan Freeman, Bob Gunton"
    }

    for key in expected_movie:
        if movie.get(key) != expected_movie[key]:
            print(movie.get(key), "was not equal to", expected_movie[key])
            return False
    return True


def get_top_rated_imdb_hits(url, file_name):
    print("------" + url + "------")
    soup = get_web_page_content(url, True)
    movie_list = soup.find('ul', attrs={'class': 'compact-list-view'})

    with open('movies.json', 'r') as infile:
        movies = list(json.load(infile))

    if len(movies) == 250:
        print("All movies were scraped last time, updating all...")
        movies = []

    try:
        for index, item in enumerate(movie_list.findAll('li', attrs={'class': 'ipc-metadata-list-summary-item'})):
            if index < len(movies):
                print(movies[index]["name"], "is already scraped, skipping...")
                continue
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

            if index == 0:
                if not are_valid_shawshank_details(movie):
                    break

            movies.append(movie)
    except Exception as e:
        print("Failed to scrape until movie with rank", len(movies))
        print(e)

    # Creates a json file with all the information that you extracted
    with open(file_name, 'w') as outfile:
        json.dump(movies, outfile, indent=4)

    if len(movies) != 250:
        print("Failed to scrape all movies, only got", len(movies), ". Will not store in db")
        get_top_rated_imdb_hits(url, file_name)

    print("----------- scraped movie list -----------")
    print("-------- uploading new list to db --------")
    uploadtoDB.upload_to_db(movies)

    # print("-------- uploading new streamables --------")
    # getStreamable.uploadNewStreamables()


get_top_rated_imdb_hits(top_movies_url, 'movies.json')
# get_top_rated_imdb_hits(top_tv_shows_url, 'tv_shows.json')
print('--------- Extraction of data is complete. Check JSON file. ---------')
