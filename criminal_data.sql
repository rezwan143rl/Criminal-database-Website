CREATE TABLE criminals 
(
    criminal_id INT PRIMARY KEY AUTO_INCREMENT,
    full_name VARCHAR(50) NOT NULL,
    gender ENUM('MALE','FEMALE','INTERSEX'),
    address TEXT,
    nid VARCHAR(10) UNIQUE,
    status ENUM('FUGITIVE','IMPRISONED','ONGOING',"FREE") DEFAULT 'ONGOING'
);

CREATE TABLE officers
(
	officer_id INT PRIMARY KEY AUTO_INCREMENT,
    full_name VARCHAR(50) NOT NULL,
    user_name VARCHAR(10) NOT NULL,
    password VARCHAR(32) NOT NULL,
    rank VARCHAR(50) NOT NULL,
    branch VARCHAR(50) NOT NULL
);

CREATE TABLE cases
(
	case_id INT PRIMARY KEY AUTO_INCREMENT,
    case_title VARCHAR(150) NOT NULL,
    case_status ENUM('OPEN','CLOSED','ONGOING'),
    open_date DATE NOT NULL,
    description TEXT NOT NULL,
    close_date DATE,
    case_officer INT 
);

CREATE TABLE crimes
(
	crime_id INT PRIMARY KEY AUTO_INCREMENT,
    case_id INT,
    crime_title VARCHAR(100),
  	crime_date DATE
);

CREATE TABLE victims
(
	victim_id INT PRIMARY KEY AUTO_INCREMENT,
    full_name VARCHAR(50) NOT NULL,
    sex ENUM('MALE','FEMALE','OTHER'),
    case_id INT
);

CREATE TABLE evidence
(
	ev_id INT PRIMARY KEY AUTO_INCREMENT,
    case_id INT,
    description TEXT NOT NULL,
    file_location VARCHAR(100)
);

CREATE TABLE criminal_case
(
	criminal_id INT,
    case_id INT,
    role VARCHAR(100)
);


ALTER TABLE crimes
ADD CONSTRAINT
fk_crimes_case_id FOREIGN KEY (case_id) REFERENCES cases(case_id) 
ON UPDATE CASCADE ON DELETE CASCADE;

ALTER TABLE victims
ADD CONSTRAINT 
fk_victims_case_id FOREIGN KEY (case_id) REFERENCES cases(case_id) 
ON UPDATE CASCADE ON DELETE CASCADE;

ALTER TABLE evidence
ADD CONSTRAINT
fk_evidence_case_id FOREIGN KEY (case_id) REFERENCES cases(case_id)
ON UPDATE CASCADE ON DELETE CASCADE;

ALTER TABLE criminal_case
ADD CONSTRAINT
fk_criminal_id FOREIGN KEY(criminal_id) REFERENCES criminals(criminal_id)
ON UPDATE CASCADE ON DELETE CASCADE,
ADD CONSTRAINT
fk_case_id FOREIGN KEY(case_id) REFERENCES cases(case_id)
ON UPDATE CASCADE ON DELETE CASCADE;

ALTER TABLE criminal_case 
ADD CONSTRAINT pk_criminal_case PRIMARY KEY (criminal_id, case_id);

-- Officers
INSERT INTO officers (officer_id, full_name, user_name, password, rank, branch) VALUES
(10000, 'Md. Anisur Rahman', 'anis100', 'pass123', 'OC', 'Dhaka Metropolitan Police'),
(10001, 'Sharmin Akhter', 'sharminA', 'pass456', 'SI', 'Chattogram Metropolitan Police'),
(10002, 'Jahirul Islam', 'jahirulI', 'pass789', 'ASI', 'Rajshahi Police'),
(10003, 'Abdur Rahman', 'rahmanC', 'pass321', 'Constable', 'Sylhet Police');

-- Criminals
INSERT INTO criminals (criminal_id, full_name, d_o_b, gender, address, nid, status) VALUES
(2000, 'Rafiqul Islam', '1985-02-15', 'MALE', 'Mirpur, Dhaka', '1234567890', 'IMPRISONED'),
(2001, 'Shahina Begum', '1990-08-21', 'FEMALE', 'Agrabad, Chattogram', '2345678901', 'ONGOING'),
(2002, 'Kamrul Hasan', '1982-05-11', 'MALE', 'Boalia, Rajshahi', '3456789012', 'FUGITIVE');

-- Cases
INSERT INTO cases (case_id, case_title, case_status, description, open_date, close_date) VALUES
(1000, 'Bank Robbery at Motijheel', 'OPEN', 'Armed robbery at Motijheel commercial area', '2023-05-10', NULL),
(1001, 'Hit and Run Accident', 'CLOSED', 'Fatal accident near Gulshan-2, Dhaka', '2022-11-20', '2022-12-05'),
(1002, 'Drug Trafficking Case', 'ONGOING', 'Seizure of yaba tablets in Teknaf', '2023-08-14', NULL);

-- Crimes
INSERT INTO crimes (crime_id, case_id, crime_title, crime_date) VALUES
(1, 1000, 'Armed Robbery', '2023-05-10'),
(2, 1001, 'Road Accident', '2022-11-20'),
(3, 1002, 'Drug Smuggling', '2023-08-14');

-- Victims
INSERT INTO victims (victim_id, full_name, sex, case_id) VALUES
(3000, 'Khaled Mahmud', 'MALE', 1000),
(3001, 'Nasrin Jahan', 'FEMALE', 1001),
(3002, 'Abdul Karim', 'MALE', 1002);

-- Evidence
INSERT INTO evidence (ev_id, case_id, description, file_location) VALUES
(1, 1000, 'CCTV footage from Motijheel', 'files/evidence/cctv_motijheel.mp4'),
(2, 1001, 'Car fragments from crash site', 'files/evidence/car_parts.jpg'),
(3, 1002, 'Confiscated yaba tablets', 'files/evidence/drugs.jpg');

-- Criminal-Case Relation
INSERT INTO criminal_case (criminal_id, case_id, role) VALUES
(2000, 1000, 'Prime Suspect'),
(2001, 1001, 'Suspect'),
(2002, 1002, 'Accused');
