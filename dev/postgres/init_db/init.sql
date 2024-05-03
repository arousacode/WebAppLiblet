CREATE TABLE "UserData"(
    id serial PRIMARY KEY,
    "sureName" text,
    age int,
    height decimal,
    "dateOfBirth" timestamp,
    "exitTime" time,
    "birthDay" date,
    description text,
    chief boolean not null default false,
    question boolean

);

CREATE SCHEMA "Test";

CREATE TABLE "Test"."UserData2"(
    id serial PRIMARY KEY,
    "sureName" text,
    age int,
    height decimal,
    "dateOfBirth" timestamp,
    "exitTime" time,
    "birthDay" date,
    description text,
    chief boolean not null default false,
    question boolean

);