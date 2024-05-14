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

CREATE TABLE "Test"."Options"(
    id serial PRIMARY KEY,
    "value" text,
    "description" text
);

INSERT INTO  "Test"."Options" (value,description) VALUES ('Value1', 'Desc1');
INSERT INTO  "Test"."Options" (value,description) VALUES ('Value2', 'Desc2');
INSERT INTO  "Test"."Options" (value,description) VALUES ('Value3', 'Desc3');