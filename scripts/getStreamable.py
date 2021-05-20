#!/usr/bin/python3

from justwatch import JustWatch

import json
import mysql.connector as mysql
from mysql.connector import Error

hostName = "hostname"
dbName = "database"
userName = "username"
password = "password"


def uploadNewStreamables():
    # Connect to the DB
    db_connection = None
    try:
        db_connection = mysql.connect(host=hostName, database=dbName, user=userName, passwd=password)
        db_cursor = db_connection.cursor()
    except Error as e:
        print("Could not connect to the database: ", e)
        exit()

    removeStreamables(db_connection, db_cursor)

    movies = getMovies(db_cursor)

    for movie in movies:
        streamable = getStreamables(movie)
        if streamable is not None:
            for streamsite in streamable:
                print(streamsite)
                checkStreamsite(db_connection, db_cursor, streamsite)
                uploadStreamable(db_connection, db_cursor, streamsite, movie)

    print("----------- upload done -----------")

def removeStreamables(conn, curs):
    query = "DELETE FROM streamable WHERE movieID > 0"
    execute_query(conn, curs, query)
    print("Removed old streamables")


def checkStreamsite(conn, curs, stream):
    query = "SELECT COUNT(streamsiteID) FROM streamsite WHERE streamsiteID = " + str(stream['providerID'])
    curs.execute(query)
    streamsiteID = curs.fetchone()
    if (streamsiteID[0] == 0):
        insertStreamsite(conn, curs, stream)


def insertStreamsite(conn, curs, site):
    siteName = '"' + site["url"][10:20] + '"'
    query = "INSERT INTO streamsite VALUES (" + str(site['providerID']) + ", " + siteName + ")"
    execute_query(conn, curs, query)


def execute_query(conn, curs, query):
    try:
        curs.execute(query)
        conn.commit()
    except Error as e:
        print("Could not commit query: ", e)


def uploadStreamable(conn, curs, streams, movie):
    query = insertStreamable(streams, movie[0])
    execute_query(conn, curs, query)


def insertStreamable(stream, movieID):
    siteName = '"' + stream["url"] + '"'
    query = "INSERT INTO streamable VALUES (" + str(movieID) + ", " + str(stream['providerID']) + ", " + siteName + ")"
    return query


def getMovies(curs):
    query = "SELECT movieID, movieName FROM movie WHERE movieRank > 0 ORDER BY movieRank"
    curs.execute(query)
    return curs.fetchall()


def getStreamables(movie):
    just_watch = JustWatch(country='SE')
    print(movie[1])
    results = just_watch.search_for_item(query=movie[1], content_types=['movie'])

    try:
        items = results['items']
        item = items[0]
        offers = item['offers']
    except:
        print("Could not find any offers")
        return None

    streams = []
    for x in offers:
        stream = {}
        if (x['monetization_type'] == 'flatrate' or x['monetization_type'] == 'free'):
            stream['providerID'] = x['provider_id']
            url = x['urls']
            stream['url'] = url['standard_web']
            streams.append(stream)

    streams = removeDupes(streams)

    return streams


def removeDupes(streams):
    res = []
    seenIds = []
    for i in streams:
        if i['providerID'] not in seenIds:
            seenIds.append(i['providerID'])
            res.append(i)
    return res
