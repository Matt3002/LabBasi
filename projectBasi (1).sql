DROP DATABASE IF EXISTS BOSTARTER;
CREATE DATABASE IF NOT EXISTS BOSTARTER;
USE BOSTARTER;

-- Tabella Utente
CREATE TABLE Utente (
    email VARCHAR(50) PRIMARY KEY,
    nickname VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(50) NOT NULL,
    nome VARCHAR(50) NOT NULL,
    cognome VARCHAR(50) NOT NULL,
    anno_Nascita INT NOT NULL,
    luogo_Nascita VARCHAR(50) NOT NULL
)
engine= "InnoDB";

-- Tabella Skill
CREATE TABLE Skill (
    competenza VARCHAR(50) PRIMARY KEY,
    email_Amministratore VARCHAR(50) NOT NULL,
    FOREIGN KEY (email_Amministratore) REFERENCES Utente(email)
)
engine= "InnoDB";

-- Tabella Curriculum
CREATE TABLE Curriculum (
    email_Utente VARCHAR(50) NOT NULL,
    nome_Competenza VARCHAR(50) NOT NULL,
    livello INT NOT NULL, -- Il valore compreso tra 0 e 5 è stato gestitio nelle procedure
    PRIMARY KEY (email_Utente, nome_Competenza),
    FOREIGN KEY (email_Utente) REFERENCES Utente(email),
    FOREIGN KEY (nome_Competenza) REFERENCES Skill(competenza)
)
engine= "InnoDB";

-- Tabella Amministratore
CREATE TABLE Amministratore (
    email_Utente VARCHAR(50) PRIMARY KEY,
    codice_Sicurezza VARCHAR(50) NOT NULL,
    FOREIGN KEY (email_Utente) REFERENCES Utente(email)
);

-- Tabella Creatore
CREATE TABLE Creatore (
    email_Utente VARCHAR(50) PRIMARY KEY,
    nr_progetti INT DEFAULT 0,
    affidabilita FLOAT, -- Da Rivedere
    FOREIGN KEY (email_Utente) REFERENCES Utente(email)
)
engine= "InnoDB";

-- Tabella Progetto
CREATE TABLE Progetto (
    nome VARCHAR(50) PRIMARY KEY,
    descrizione TEXT NOT NULL,
    data_Inserimento DATE NOT NULL,
    budget FLOAT NOT NULL, 
    data_Limite DATE NOT NULL,
    stato ENUM('aperto', 'chiuso') DEFAULT 'aperto',
    tipo ENUM('hardware', 'software') NOT NULL,
    email_Creatore VARCHAR(50) NOT NULL,
    FOREIGN KEY (email_Creatore) REFERENCES Creatore(email_Utente)
)
engine= "InnoDB";

CREATE TABLE Foto(
	id INT AUTO_INCREMENT PRIMARY KEY,
	immagine BLOB,
    nome_Progetto VARCHAR(50) NOT NULL,
    FOREIGN KEY (nome_Progetto) REFERENCES Progetto(nome)
    )
engine= "InnoDB";
    

-- Tabella Reward
CREATE TABLE Reward (
    codice INT AUTO_INCREMENT PRIMARY KEY,
    descrizione TEXT NOT NULL,
    foto BLOB,
    nome_Progetto VARCHAR(50) NOT NULL,
    FOREIGN KEY (nome_Progetto) REFERENCES Progetto(nome)
)
engine= "InnoDB";

-- Tabella Componente
CREATE TABLE Componente (
    nome VARCHAR(50) PRIMARY KEY,
    descrizione TEXT NOT NULL,
    prezzo FLOAT NOT NULL
)
engine= "InnoDB";

-- Tabella Lista_Componenti
CREATE TABLE Lista_Componenti (
    nome_Componente VARCHAR(50) NOT NULL,
    nome_Progetto VARCHAR(50) NOT NULL,
    quantita INT NOT NULL,
    PRIMARY KEY (nome_Componente, nome_Progetto),
    FOREIGN KEY (nome_Componente) REFERENCES Componente(nome),
    FOREIGN KEY (nome_Progetto) REFERENCES Progetto(nome)
)
engine= "InnoDB";

-- Tabella Software
CREATE TABLE Software (
    nome_Progetto VARCHAR(50) PRIMARY KEY,
    FOREIGN KEY (nome_Progetto) REFERENCES Progetto(nome)
)
engine= "InnoDB";

-- Tabella Profilo
CREATE TABLE Profilo (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(50) NOT NULL,
    nome_Software VARCHAR(50) NOT NULL,
    FOREIGN KEY (nome_Software) REFERENCES Software(nome_progetto)
)
engine= "InnoDB";

CREATE TABLE Profilo_Software(
	nome_Software VARCHAR(50),
    id_Profilo INT AUTO_INCREMENT,
	PRIMARY KEY (nome_Software, id_Profilo),
    FOREIGN KEY (nome_Software) REFERENCES Software(nome_progetto),
    FOREIGN KEY (id_Profilo) REFERENCES Profilo(id)
)
engine= "InnoDB";

-- Tabella ProfiloSkill
CREATE TABLE ProfiloSkill (
    id_Profilo INT NOT NULL,
    nome_Competenza VARCHAR(50) NOT NULL,
    livello INT,
    PRIMARY KEY (id_Profilo, nome_Competenza),
    FOREIGN KEY (id_Profilo) REFERENCES Profilo(id),
    FOREIGN KEY (nome_Competenza) REFERENCES Skill(competenza)
)
engine= "InnoDB";

-- Tabella Finanziamento
CREATE TABLE Finanziamento (
    email_Utente VARCHAR(50) NOT NULL,
    nome_Progetto VARCHAR(50) NOT NULL,
    importo FLOAT NOT NULL, 
    data DATE NOT NULL,
    codice_Reward INT,
    PRIMARY KEY (email_Utente, nome_Progetto, data),
    FOREIGN KEY (email_Utente) REFERENCES Utente(email),
    FOREIGN KEY (nome_Progetto) REFERENCES Progetto(nome),
    FOREIGN KEY (codice_Reward) REFERENCES Reward(codice)
)
engine= "InnoDB";

-- Tabella Commento
CREATE TABLE Commento (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email_Utente VARCHAR(50) NOT NULL,
    nome_Progetto VARCHAR(50) NOT NULL,
    data DATE NOT NULL,
    testo TEXT NOT NULL,
    FOREIGN KEY (email_Utente) REFERENCES Utente(email),
    FOREIGN KEY (nome_Progetto) REFERENCES Progetto(nome)
)
engine= "InnoDB";

-- Tabella Risposta
CREATE TABLE Risposta (
    id_Commento INT PRIMARY KEY,
    testo TEXT NOT NULL,
    email_Creatore VARCHAR(50) NOT NULL,
    FOREIGN KEY (id_Commento) REFERENCES Commento(id),
    FOREIGN KEY (email_Creatore) REFERENCES Creatore(email_Utente)
)
engine= "InnoDB";

-- Tabella Candidatura
CREATE TABLE Candidatura (
    email_Utente VARCHAR(50) NOT NULL,
    id_Profilo INT NOT NULL,
    stato ENUM('accettata', 'rifiutata', 'in attesa') DEFAULT 'in attesa',
    nome_Progetto VARCHAR(50) NOT NULL,
    PRIMARY KEY (email_Utente, id_Profilo, nome_Progetto),
    FOREIGN KEY (email_Utente) REFERENCES Utente(email),
    FOREIGN KEY (id_Profilo) REFERENCES Profilo(id),
    FOREIGN KEY (nome_Progetto) REFERENCES Progetto(nome)
)
engine= "InnoDB";


-- Inserimento dati nella tabella Utente
INSERT INTO Utente (email, nickname, password, nome, cognome, anno_Nascita, luogo_Nascita) VALUES
('mario.rossi@email.com', 'MarioR', 'pass123', 'Mario', 'Rossi', 1995, 'Roma'),
('lucia.bianchi@email.com', 'LuciaB', 'securepass', 'Lucia', 'Bianchi', 1998, 'Milano'),
('giovanni.verdi@email.com', 'GioV', 'mypassword', 'Giovanni', 'Verdi', 1992, 'Napoli'),
('anna.neri@email.com', 'AnnaN', 'passanna', 'Anna', 'Neri', 2000, 'Torino'),
('paolo.gialli@email.com', 'PaoloG', 'paopass', 'Paolo', 'Gialli', 1993, 'Bologna');

-- Inserimento dati nella tabella Amministratore
INSERT INTO Amministratore (email_Utente, codice_Sicurezza) VALUES
('mario.rossi@email.com', 'admin123'),
('lucia.bianchi@email.com', 'secure456');

-- Inserimento dati nella tabella Skill
INSERT INTO Skill (competenza, email_Amministratore) VALUES
('Python', 'mario.rossi@email.com'),
('Java', 'lucia.bianchi@email.com'),
('HTML', 'mario.rossi@email.com'),
('AI', 'lucia.bianchi@email.com');

-- Inserimento dati nella tabella Curriculum
INSERT INTO Curriculum (email_Utente, nome_Competenza, livello) VALUES
('giovanni.verdi@email.com', 'Python', 4),
('anna.neri@email.com', 'Java', 3),
('paolo.gialli@email.com', 'HTML', 5),
('mario.rossi@email.com', 'AI', 2);

-- Inserimento dati nella tabella Creatore
INSERT INTO Creatore (email_Utente, nr_progetti, affidabilita) VALUES
('giovanni.verdi@email.com', 2, 80.00),
('anna.neri@email.com', 3, 90.00);

-- Inserimento dati nella tabella Progetto
INSERT INTO Progetto (nome, descrizione, data_Inserimento, budget, data_Limite, stato, tipo, email_Creatore) VALUES
('Smartwatch AI', 'Un nuovo smartwatch con AI integrata', '2025-02-01', 10000.00, '2025-06-01', 'aperto', 'hardware', 'giovanni.verdi@email.com'),
('E-commerce Sicuro', 'Piattaforma di e-commerce con AI', '2025-01-10', 15000.00, '2025-05-15', 'aperto', 'software', 'anna.neri@email.com');

-- Inserimento dati nella tabella Reward
INSERT INTO Reward (descrizione, foto, nome_Progetto) VALUES
('Maglietta ufficiale del progetto', 'maglietta.png', 'Smartwatch AI'),
('Accesso anticipato al software', 'accesso.png', 'E-commerce Sicuro');

-- Inserimento dati nella tabella Finanziamento
INSERT INTO Finanziamento (email_Utente, nome_Progetto, importo, data, codice_Reward) VALUES
('paolo.gialli@email.com', 'Smartwatch AI', 500.00, '2025-02-05', 1),
('mario.rossi@email.com', 'E-commerce Sicuro', 1000.00, '2025-02-07', 2);

-- Inserimento dati nella tabella Commento
INSERT INTO Commento (email_Utente, nome_Progetto, data, testo) VALUES
('paolo.gialli@email.com', 'Smartwatch AI', '2025-02-10', 'Idea interessante!'),
('lucia.bianchi@email.com', 'E-commerce Sicuro', '2025-02-12', 'Sembra promettente!');


-- Stored Procedure per registrare un nuovo utente con controlli
DELIMITER //
CREATE PROCEDURE RegistrazioneUtente(
    IN p_email VARCHAR(50),
    IN p_nickname VARCHAR(50),
    IN p_password VARCHAR(50),
    IN p_nome VARCHAR(50),
    IN p_cognome VARCHAR(50),
    IN p_anno_nascita INT,
    IN p_luogo_nascita VARCHAR(50)
)
BEGIN
    -- Controllo che la mail sia nel formato corretto usando LIKE con _ per caratteri arbitrari
    IF p_email NOT LIKE '_%@_%._%' THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Errore: Email non valida';
    END IF;
    
    -- Controllo che l'utente abbia almeno 18 anni (anno corrente fissato al 2025)
    IF (2025 - p_anno_nascita) < 18 THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Errore: Devi avere almeno 18 anni per registrarti';
    END IF;
    
    INSERT INTO Utente (email, nickname, password, nome, cognome, anno_Nascita, luogo_Nascita)
    VALUES (p_email, p_nickname, p_password, p_nome, p_cognome, p_anno_nascita, p_luogo_nascita);
END //
DELIMITER ;

-- Stored Procedure per autenticazione di un utente
DELIMITER //
CREATE PROCEDURE AutenticazioneUtente(
    IN p_email VARCHAR(50),
    IN p_password VARCHAR(50)
)
BEGIN
    IF NOT EXISTS (SELECT 1 FROM Utente WHERE email = p_email AND password = p_password) THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Errore: Credenziali non valide';
    END IF;
END //
DELIMITER ;

-- Stored Procedure per autenticazione di un creatore
DELIMITER //
CREATE PROCEDURE AutenticazioneCreatore(
    IN p_email VARCHAR(50),
    IN p_password VARCHAR(50)
)
BEGIN
    IF NOT EXISTS (
        SELECT 1
        FROM Utente U
        INNER JOIN Creatore C ON U.email = C.email_Utente
        WHERE U.email = p_email AND U.password = p_password
    ) THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Errore: Credenziali non valide o utente non è un creatore';
    END IF;
END //
DELIMITER ;

-- Stored Procedure per aggiungere una skill al curriculum con controllo livello
DELIMITER //
CREATE PROCEDURE AggiungiSkillCurriculum(
    IN p_email_Utente VARCHAR(50),
    IN p_nome_competenza VARCHAR(50),
    IN p_livello INT
)
BEGIN
    -- Controllo che il livello sia compreso tra 0 e 5
    IF p_livello < 0 OR p_livello > 5 THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Errore: Il livello deve essere compreso tra 0 e 5';
    END IF;
    
    INSERT INTO Curriculum (email_Utente, nome_Competenza, livello)
    VALUES (p_email_Utente, p_nome_competenza, p_livello);
END //
DELIMITER ;

-- Stored Procedure per visualizzare i progetti disponibili
DELIMITER //
CREATE PROCEDURE VisualizzaProgetti()
BEGIN
    SELECT * FROM Progetto WHERE stato = 'aperto';
END //
DELIMITER ;


-- Stored Procedure per finanziare un progetto con controllo sull'importo
DELIMITER //
CREATE PROCEDURE FinanziaProgetto(
    IN p_email_Utente VARCHAR(50),
    IN p_nome_Progetto VARCHAR(50),
    IN p_importo FLOAT,
    IN p_data DATE,
    IN p_codice_Reward INT
)
BEGIN

    DECLARE v_budget FLOAT;
    DECLARE v_fondi_raccolti FLOAT;
    DECLARE v_nuovo_totale FLOAT;

    -- Verifica che l'importo sia positivo
    IF p_importo <= 0 THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = "Errore: L'importo deve essere positivo";
    END IF;

    -- Recupera il budget del progetto
    SELECT budget INTO v_budget FROM Progetto WHERE nome = p_nome_Progetto;

    -- Calcola il totale attuale dei finanziamenti ricevuti
    SELECT COALESCE(SUM(importo), 0) INTO v_fondi_raccolti
    FROM Finanziamento WHERE nome_Progetto = p_nome_Progetto;

    -- Verifica che il nuovo finanziamento non superi il budget
    SET v_nuovo_totale = v_fondi_raccolti + p_importo;
    IF v_nuovo_totale > v_budget THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Errore: Il finanziamento supererebbe il budget del progetto';
    END IF;

    -- Verifica che la data sia valida
    IF p_data > (SELECT data_limite FROM Progetto WHERE nome = p_nome_Progetto) THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Errore: Non è possibile finanziare un progetto oltre la data limite';
    END IF;

    -- Inserisce il finanziamento
    INSERT INTO Finanziamento (email_Utente, nome_Progetto, importo, data, codice_Reward)
    VALUES (p_email_Utente, p_nome_Progetto, p_importo, p_data, p_codice_Reward);
END //
DELIMITER ;

-- Stored Procedure per selezionare una reward dopo un finanziamento
DELIMITER //
CREATE PROCEDURE SelezionaReward(
    IN p_email_Utente VARCHAR(50),
    IN p_nome_Progetto VARCHAR(50),
    IN p_codice_Reward INT
)
BEGIN
    UPDATE Finanziamento
    SET codice_Reward = p_codice_Reward
    WHERE email_Utente = p_email_Utente AND nome_Progetto = p_nome_Progetto;
END //
DELIMITER ;

-- Stored Procedure per aggiungere un commento a un progetto
DELIMITER //
CREATE PROCEDURE AggiungiCommento(
    IN p_email_Utente VARCHAR(50),
    IN p_nome_Progetto VARCHAR(50),
    IN p_data DATE,
    IN p_testo TEXT
)
BEGIN
    INSERT INTO Commento (email_Utente, nome_Progetto, data, testo)
    VALUES (p_email_Utente, p_nome_Progetto, p_data, p_testo);
END //
DELIMITER ;

-- Stored Procedure per inviare una candidatura per un profilo di un progetto software con verifica skill
DELIMITER //
CREATE PROCEDURE InviaCandidatura(
    IN p_email_Utente VARCHAR(50),
    IN p_id_Profilo INT,
    IN p_nome_Progetto VARCHAR(50)
)
BEGIN
    DECLARE v_skill_match INT;
    DECLARE v_profile_match INT;

    -- Verifica che il profilo sia richiesto dal progetto per cui si sta inviando la candidatura
    SELECT COUNT(*) INTO v_profile_match
    FROM Profilo_Software PS
    WHERE PS.id_Profilo = p_id_Profilo AND PS.nome_Software=p_nome_Progetto;

    IF v_profile_match < 1 THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = "Errore: Il progetto non richiede il profilo per cui l'utente sta facendo richiesta";
    END IF;
    
    -- Verifica che l'utente soddisfi i requisiti delle skill richieste dal profilo
    SELECT COUNT(*) INTO v_skill_match
    FROM ProfiloSkill PS
    JOIN Curriculum C ON PS.nome_competenza = C.nome_competenza
    WHERE PS.id_Profilo = p_id_Profilo
    AND C.email_Utente = p_email_Utente
    AND C.livello >= PS.livello;
    
    IF v_skill_match < (SELECT COUNT(*) FROM ProfiloSkill WHERE id_profilo = p_id_Profilo) THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = "Errore: L'utente non soddisfa i requisiti di skill richiesti per il profilo";
    END IF;
    
    -- Inserisce la candidatura con l'email del creatore recuperata
    INSERT INTO Candidatura (email_Utente, id_Profilo, stato, nome_Progetto)
    VALUES (p_email_Utente, p_id_Profilo, 'in attesa', p_nome_Progetto);
END //
DELIMITER ;

DELIMITER //
CREATE PROCEDURE AggiungiCompetenza(
    IN p_competenza VARCHAR(50),
    IN p_email_Amministratore VARCHAR(50)
)
BEGIN
    -- Verifica se l'utente è amministratore
    IF EXISTS (SELECT 1 FROM Amministratore WHERE email_Utente = p_email_Amministratore) THEN
        INSERT INTO Skill (competenza, email_Amministratore)
        VALUES (p_competenza, p_email_Amministratore);
    ELSE
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Errore: Solo un amministratore può aggiungere una competenza.';
    END IF;
END //
DELIMITER ;

-- Stored Procedure per autenticazione amministratore con codice di sicurezza
DELIMITER //
CREATE PROCEDURE AutenticazioneAmministratore(
    IN p_email VARCHAR(50),
    IN p_password VARCHAR(50),
    IN p_codice_sicurezza VARCHAR(50)
)
BEGIN
    IF NOT EXISTS (
        SELECT 1 FROM Amministratore A JOIN Utente U ON A.email_Utente = U.email
        WHERE U.email = p_email AND U.password = p_password AND A.codice_sicurezza = p_codice_sicurezza
    ) THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Errore: Credenziali non valide o codice di sicurezza errato.';
    END IF;
END //
DELIMITER ;

DELIMITER //
CREATE PROCEDURE InserisciProgetto(
    IN p_nome VARCHAR(50),
    IN p_descrizione TEXT,
    IN p_data_inserimento DATE,
    IN p_budget FLOAT,
    IN p_data_limite DATE,
    IN p_stato ENUM('aperto', 'chiuso'),
    IN p_tipo ENUM('hardware', 'software'),
    IN p_email_creatore VARCHAR(255)
)
BEGIN
    IF EXISTS (SELECT 1 FROM Creatore WHERE email_Utente = p_email_creatore) THEN
        INSERT INTO Progetto (nome, descrizione, data_Inserimento, budget, data_Limite, stato, tipo, email_Creatore)
        VALUES (p_nome, p_descrizione, p_data_inserimento, p_budget, p_data_limite, p_stato, p_tipo, p_email_creatore);
    ELSE
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Errore: Solo un creatore può inserire un progetto.';
    END IF;
END //
DELIMITER ;

-- Stored Procedure per inserire una reward per un progetto
DELIMITER //
CREATE PROCEDURE InserisciReward(
    IN p_descrizione TEXT,
    IN p_foto VARCHAR(255),
    IN p_nome_Progetto VARCHAR(50)
)
BEGIN
    INSERT INTO Reward (descrizione, foto, nome_Progetto)
    VALUES (p_descrizione, p_foto, p_nome_Progetto);
END //
DELIMITER ;

-- Stored Procedure per rispondere a un commento
DELIMITER //
CREATE PROCEDURE RispondiCommento(
    IN p_id_Commento INT,
    IN p_testo TEXT,
    IN p_email_Creatore VARCHAR(50)
)
BEGIN
    INSERT INTO Risposta (id_Commento, testo, email_Creatore)
    VALUES (p_id_Commento, p_testo, p_email_Creatore);
END //
DELIMITER ;

-- Stored Procedure per inserire un profilo per un progetto software con verifica del tipo di progetto
DELIMITER //
CREATE PROCEDURE InserisciProfilo(
    IN p_nome VARCHAR(100),
    IN p_nome_Software VARCHAR(255)
)
BEGIN
    -- Verifica che il progetto sia di tipo software
    IF (SELECT tipo FROM Progetto WHERE nome = p_nome_Software) <> 'software' THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = "Errore: Il profilo può essere inserito solo per progetti di tipo software";
    END IF;
    
    INSERT INTO Profilo (nome, nome_Software)
    VALUES (p_nome, p_nome_Software);
END //
DELIMITER ;

-- Stored Procedure per accettare o rifiutare una candidatura con verifica stato e ruolo creatore
DELIMITER //
CREATE PROCEDURE GestisciCandidatura(
    IN p_email_Utente VARCHAR(50),
    IN p_id_Profilo INT,
    IN p_nome_Progetto VARCHAR(50),
    IN p_email_Creatore VARCHAR(50),
    IN p_stato ENUM('accettata', 'rifiutata')
)
BEGIN
    -- 1) Verifica che l'email appartenga a un creatore
    IF NOT EXISTS (
       SELECT 1 FROM Creatore 
        WHERE email_Utente = p_email_Creatore
    ) THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = "Errore: Solo un creatore può gestire le candidature";
    END IF;

    -- 2) Verifica che l'utente creatore passato sia davvero il creatore del progetto
    IF (SELECT email_creatore 
          FROM Progetto 
         WHERE nome = p_nome_Progetto
       ) <> p_email_Creatore THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = "Errore: Questo creatore non è proprietario del progetto indicato";
    END IF;

    -- 3) Verifica che la candidatura sia ancora in attesa
    IF (SELECT stato 
          FROM Candidatura 
         WHERE email_Utente = p_email_Utente
           AND id_Profilo   = p_id_Profilo
           AND nome_Progetto = p_nome_Progetto
       ) <> 'in attesa' THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = "Errore: La candidatura deve essere in attesa per essere aggiornata";
    END IF;

    -- 4) Aggiorna stato
    UPDATE Candidatura
       SET stato = p_stato
     WHERE email_Utente = p_email_Utente
       AND id_Profilo   = p_id_Profilo
       AND nome_Progetto = p_nome_Progetto;
END //
DELIMITER ;

-- Vista per la classifica dei creatori per affidabilità
CREATE VIEW ClassificaCreatori AS
SELECT U.nickname, C.affidabilita
FROM Creatore C
JOIN Utente U ON C.email_Utente = U.email
ORDER BY C.affidabilita DESC
LIMIT 3;

-- Vista di appoggio per il totale finanziamenti per progetto
CREATE VIEW TotaleFinanziamenti AS
SELECT nome_Progetto, SUM(importo) AS totale_fondi
FROM Finanziamento
WHERE nome_Progetto IS NOT NULL
GROUP BY nome_Progetto;

-- Vista per i progetti aperti più vicini al completamento
CREATE VIEW ProgettiViciniCompletamento AS
SELECT P.nome, P.budget, COALESCE(TF.totale_fondi, 0) AS totale_fondi, (P.budget - COALESCE(TF.totale_fondi, 0)) AS differenza
FROM Progetto P
LEFT JOIN TotaleFinanziamenti TF ON P.nome = TF.nome_Progetto
WHERE P.stato = 'aperto'
ORDER BY differenza ASC
LIMIT 3;

-- Vista di appoggio per il totale finanziamenti per utente
CREATE VIEW TotaleFinanziamentiUtente AS
SELECT email_Utente, SUM(importo) AS totale_finanziato
FROM Finanziamento
WHERE email_Utente IS NOT NULL
GROUP BY email_Utente;

-- Vista per la classifica degli utenti per totale finanziamenti erogati
CREATE VIEW ClassificaFinanziatori AS
SELECT U.nickname, COALESCE(TFU.totale_finanziato, 0) AS totale_finanziato
FROM Utente U
LEFT JOIN TotaleFinanziamentiUtente TFU ON U.email = TFU.email_Utente
ORDER BY TFU.totale_finanziato DESC
LIMIT 3;


-- Trigger per aggiornare l'affidabilità dei creatori
DELIMITER //
CREATE TRIGGER AggiornaAffidabilitaDopoFinanziamento
AFTER INSERT ON Finanziamento
FOR EACH ROW
BEGIN
    DECLARE v_progetti_totali INT;
    DECLARE v_progetti_finanziati INT;
    
    -- Conta il numero totale di progetti creati dal creatore
    SELECT nr_progetti INTO v_progetti_totali
    FROM Creatore C
    WHERE C.email_Utente=(SELECT email_Creatore FROM Progetto WHERE nome=NEW.nome_Progetto);
    
    -- Conta il numero di progetti che hanno almeno un finanziamento
    SELECT COUNT(DISTINCT F.nome_Progetto) INTO v_progetti_finanziati
    FROM Finanziamento F
    JOIN Progetto P ON F.nome_Progetto = P.nome
    WHERE P.email_creatore = (SELECT email_Creatore FROM Progetto WHERE nome = NEW.nome_Progetto);
    
    -- Aggiorna l'affidabilità del creatore
    UPDATE Creatore
    SET affidabilita = (v_progetti_finanziati * 100) / v_progetti_totali
    WHERE email_Utente = (SELECT email_Creatore FROM Progetto WHERE nome = NEW.nome_Progetto);
END //
DELIMITER ;

-- Trigger per chiudere automaticamente i progetti quando raggiungono il budget
DELIMITER //
CREATE TRIGGER ChiudiProgettoRaggiuntoBudget
AFTER INSERT ON Finanziamento
FOR EACH ROW
BEGIN
    DECLARE v_budget_totale FLOAT;
    DECLARE v_fondi_raccolti FLOAT;
    
    -- Ottiene il budget del progetto
    SELECT budget INTO v_budget_totale
    FROM Progetto
    WHERE nome = NEW.nome_Progetto;
    
    -- Calcola il totale dei finanziamenti ricevuti
    SELECT SUM(importo) INTO v_fondi_raccolti
    FROM Finanziamento
    WHERE nome_Progetto = NEW.nome_Progetto;
    
    -- Se i fondi raccolti raggiungono o superano il budget, chiude il progetto
    IF v_fondi_raccolti >= v_budget_totale THEN
        UPDATE Progetto
        SET stato = 'chiuso'
        WHERE nome = NEW.nome_Progetto;
    END IF;
END //
DELIMITER ; 

-- Trigger per incrementare automaticamente il numero di progetti di un creatore
DELIMITER //
CREATE TRIGGER IncrementaNumeroProgetti
AFTER INSERT ON Progetto
FOR EACH ROW
BEGIN
    -- Incrementa il numero totale di progetti per il creatore
    UPDATE Creatore
    SET nr_progetti = nr_progetti + 1
    WHERE email_Utente = NEW.email_Creatore;
END //
DELIMITER ;
-- Trigger per garantire che quantita della tabella componente sia >0
DELIMITER $$

CREATE TRIGGER check_quantita_componente
BEFORE INSERT ON Lista_Componenti
FOR EACH ROW
BEGIN
    IF NEW.quantita <= 0 THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'La quantità deve essere maggiore di 0';
    END IF;
END $$

DELIMITER ;

DELIMITER //
CREATE EVENT ChiudiProgettiScaduti
ON SCHEDULE EVERY 1 DAY
DO
BEGIN
    UPDATE Progetto
    SET stato = 'chiuso'
    WHERE data_limite < CURDATE() AND stato = 'aperto';
END //
DELIMITER ;






