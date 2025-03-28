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


-- Tabella Profilo
CREATE TABLE Profilo (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(50) NOT NULL
)
engine= "InnoDB";

CREATE TABLE Profilo_Software(
	nome_Software VARCHAR(50),
    id_Profilo INT AUTO_INCREMENT,
	PRIMARY KEY (nome_Software, id_Profilo),
    FOREIGN KEY (nome_Software) REFERENCES Progetto(nome),
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
    -- Controllo che la mail sia nel formato corretto
    IF p_email NOT LIKE '_%@_%._%' THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Errore: Email non valida';
    END IF;

    -- Controllo che l'utente abbia almeno 18 anni (anno corrente = 2025)
    IF (2025 - p_anno_nascita) < 18 THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Errore: Devi avere almeno 18 anni per registrarti';
    END IF;

    -- Controllo se l'utente è già registrato
    IF EXISTS (SELECT 1 FROM Utente WHERE email = p_email) THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Errore: Utente già registrato con questa email';
    END IF;

    -- Inserimento dell'utente
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

-- Stored Procedure per visualizzare le skill che non hai ancora aggiunto
DELIMITER //
CREATE PROCEDURE VisualizzaSkillDisponibili(IN p_email VARCHAR(50))
BEGIN
    SELECT S.competenza
    FROM Skill S
    WHERE S.competenza NOT IN (
        SELECT C.nome_Competenza
        FROM Curriculum C
        WHERE C.email_Utente = p_email
    );
END //
DELIMITER ;

-- Stored Procedure per visualizzare i progetti disponibili
DELIMITER //
CREATE PROCEDURE VisualizzaProgetti()
BEGIN
    SELECT * FROM Progetto WHERE stato = 'aperto';
END //
DELIMITER ;

-- Stored Procedure per visualizzare i tuoi progetti
DELIMITER //
CREATE PROCEDURE VisualizzaProgettiCreati(IN p_email_creatore VARCHAR(255))
BEGIN
    SELECT * 
    FROM Progetto 
    WHERE email_Creatore = p_email_creatore;
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

    -- Verifica che non esista già un finanziamento dello stesso utente per lo stesso progetto nello stesso giorno
    IF EXISTS (
        SELECT 1
        FROM Finanziamento
        WHERE email_Utente = p_email_Utente
          AND nome_Progetto = p_nome_Progetto
          AND data = p_data
    ) THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Errore: Hai già finanziato questo progetto in questa data';
    END IF;

    -- Recupera il budget del progetto
    SELECT budget INTO v_budget
    FROM Progetto
    WHERE nome = p_nome_Progetto;

    -- Calcola il totale attuale dei finanziamenti ricevuti
    SELECT COALESCE(SUM(importo), 0) INTO v_fondi_raccolti
    FROM Finanziamento
    WHERE nome_Progetto = p_nome_Progetto;

    -- Verifica che il nuovo finanziamento non superi il budget
    SET v_nuovo_totale = v_fondi_raccolti + p_importo;
    IF v_nuovo_totale > v_budget THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Errore: Il finanziamento supererebbe il budget del progetto';
    END IF;

    -- Verifica che la data sia valida (non oltre la data limite)
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

-- Stored Procedure per visualizzare i commenti relativi a un progetto
DELIMITER //
CREATE PROCEDURE VisualizzaCommentiProgetto(IN p_nome_progetto VARCHAR(50))
BEGIN
    SELECT *
    FROM Commento 
    WHERE nome_Progetto = p_nome_progetto
    ORDER BY data DESC; -- Ordina i commenti dal più recente al più vecchio
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

    -- Verifica che il profilo sia richiesto dal progetto
    SELECT COUNT(*) INTO v_profile_match
    FROM Profilo_Software PS
    WHERE PS.id_Profilo = p_id_Profilo AND PS.nome_Software = p_nome_Progetto;

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

    -- Verifica che non esista già una candidatura per lo stesso utente, progetto e profilo
    IF EXISTS (
        SELECT 1
        FROM Candidatura
        WHERE email_Utente = p_email_Utente
          AND id_Profilo = p_id_Profilo
          AND nome_Progetto = p_nome_Progetto
    ) THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = "Errore: Hai già inviato una candidatura per questo profilo in questo progetto";
    END IF;

    -- Inserisce la candidatura
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
    -- Verifica se l'utente è un amministratore
    IF NOT EXISTS (
        SELECT 1 FROM Amministratore WHERE email_Utente = p_email_Amministratore
    ) THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Errore: Solo un amministratore può aggiungere una competenza.';
    END IF;

    -- Verifica se la competenza esiste già
    IF EXISTS (
        SELECT 1 FROM Skill WHERE competenza = p_competenza
    ) THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Errore: Questa competenza è già presente in piattaforma.';
    END IF;

    -- Inserisce la nuova competenza
    INSERT INTO Skill (competenza, email_Amministratore)
    VALUES (p_competenza, p_email_Amministratore);
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
    -- Verifica che l'utente sia un creatore
    IF EXISTS (SELECT 1 FROM Creatore WHERE email_Utente = p_email_creatore) THEN

        -- Verifica che non esista già un progetto con lo stesso nome
        IF EXISTS (SELECT 1 FROM Progetto WHERE nome = p_nome) THEN
            SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'Errore: Esiste già un progetto con questo nome.';
        END IF;

        -- Inserimento del progetto
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
('AI', 'lucia.bianchi@email.com'),
('Machine Learning', 'lucia.bianchi@email.com'),
('PHP', 'mario.rossi@email.com'),
('ReactJS', 'lucia.bianchi@email.com'),
('Cybersecurity', 'lucia.bianchi@email.com'),
('Big Data', 'lucia.bianchi@email.com'),
('UX Design', 'lucia.bianchi@email.com'),
('Networking', 'lucia.bianchi@email.com'),
('Blockchain', 'lucia.bianchi@email.com');

-- Inserimento dati nella tabella Curriculum
INSERT INTO Curriculum (email_Utente, nome_Competenza, livello) VALUES
('giovanni.verdi@email.com', 'Python', 4),
('anna.neri@email.com', 'Java', 3),
('paolo.gialli@email.com', 'HTML', 5),
('mario.rossi@email.com', 'AI', 2);

-- Inserimento dati nella tabella Creatore
INSERT INTO Creatore (email_Utente, nr_progetti, affidabilita) VALUES
('giovanni.verdi@email.com', 0, 0.00),
('anna.neri@email.com', 0, 0.00);

-- Inserimento dati nella tabella Progetto
INSERT INTO Progetto (nome, descrizione, data_Inserimento, budget, data_Limite, stato, tipo, email_Creatore) VALUES
('Smartwatch AI', 'Un nuovo smartwatch con AI integrata', '2025-02-01', 10000.00, '2025-06-01', 'aperto', 'hardware', 'giovanni.verdi@email.com'),
('E-commerce Sicuro', 'Piattaforma di e-commerce con AI', '2025-01-10', 15000.00, '2025-05-15', 'aperto', 'software', 'anna.neri@email.com'),
('App Fitness AI', 'Applicazione mobile per il fitness con AI personalizzata', '2025-03-05', 12000.00, '2025-07-10', 'aperto', 'software', 'giovanni.verdi@email.com'),
('CyberShield', 'Sistema avanzato di protezione per reti aziendali', '2025-02-20', 20000.00, '2025-09-01', 'aperto', 'hardware', 'anna.neri@email.com'),
('SmartHome Hub', 'Dispositivo IoT per la gestione della casa intelligente', '2025-04-15', 8000.00, '2025-08-20', 'aperto', 'hardware', 'giovanni.verdi@email.com'),
('Social Learning Platform', 'Piattaforma social per l apprendimento collaborativo', '2025-01-25', 18000.00, '2025-06-30', 'aperto', 'software', 'anna.neri@email.com'),
('VR Therapy', 'Applicazione di realtà virtuale per terapia psicologica', '2025-02-18', 22000.00, '2025-10-05', 'aperto', 'software', 'giovanni.verdi@email.com'),
('E-Payment Blockchain', 'Sistema di pagamento digitale basato su blockchain', '2025-03-12', 25000.00, '2025-12-01', 'aperto', 'software', 'anna.neri@email.com');

-- Inserimento ricompense aggiuntive per i progetti
INSERT INTO Reward (descrizione, foto, nome_Progetto) VALUES
-- Smartwatch AI
('Adesivi edizione limitata', 'adesivi.png', 'Smartwatch AI'),
('Poster autografato dai designer', 'poster.png', 'Smartwatch AI'),

-- E-commerce Sicuro
('Sconto del 50% sul primo acquisto', 'sconto50.png', 'E-commerce Sicuro'),
('Accesso a webinar esclusivo', 'webinar.png', 'E-commerce Sicuro'),

-- App Fitness AI
('Abbonamento Premium per 3 mesi', 'premium3.png', 'App Fitness AI'),
('Maglietta con logo Fitness AI', 'tshirt_fitness.png', 'App Fitness AI'),

-- CyberShield
('Penna USB con software di sicurezza', 'usb.png', 'CyberShield'),
('Sessione gratuita con esperto cybersecurity', 'sessione.png', 'CyberShield'),

-- SmartHome Hub
('Sticker personalizzati per i dispositivi smart', 'sticker_hub.png', 'SmartHome Hub'),
('Accesso anticipato all’app mobile', 'app_preview.png', 'SmartHome Hub'),

-- Social Learning Platform
('Profilo verificato nella piattaforma', 'badge_verificato.png', 'Social Learning Platform'),
('Invito al gruppo beta tester', 'beta_group.png', 'Social Learning Platform'),

-- VR Therapy
('Visore VR brandizzato', 'visore.png', 'VR Therapy'),
('Sessione gratuita di prova con terapeuta virtuale', 'sessione_vr.png', 'VR Therapy'),

-- E-Payment Blockchain
('NFT collezionabile del progetto', 'nft.png', 'E-Payment Blockchain'),
('Accesso esclusivo alla roadmap tecnica', 'roadmap.png', 'E-Payment Blockchain');


-- Inserimento dati nella tabella Finanziamento
INSERT INTO Finanziamento (email_Utente, nome_Progetto, importo, data, codice_Reward) VALUES
('paolo.gialli@email.com', 'Smartwatch AI', 500.00, '2025-02-05', 1),
('mario.rossi@email.com', 'E-commerce Sicuro', 1000.00, '2025-02-07', 2);

-- Inserimento dati nella tabella Commento
INSERT INTO Commento (email_Utente, nome_Progetto, data, testo) VALUES
('paolo.gialli@email.com', 'Smartwatch AI', '2025-02-10', 'Idea interessante!'),
('lucia.bianchi@email.com', 'E-commerce Sicuro', '2025-02-12', 'Sembra promettente!');


INSERT INTO Profilo (nome) VALUES 
('Esperto AI'),
('Sviluppatore Backend'),
('UI/UX Designer'),
('Cybersecurity Analyst'),
('Data Scientist'),
('Full Stack Developer'),
('Cloud Engineer'),
('Cybersecurity Expert'),
('Data Engineer'),
('AI Researcher'),
('DevOps Specialist'),
('Frontend Developer'),
('Backend Developer'),
('Network Administrator'),
('Blockchain Developer');

INSERT INTO Profilo_Software (nome_Software, id_Profilo) VALUES
-- Progetto: E-commerce Sicuro
('E-commerce Sicuro', 2),  -- Sviluppatore Backend
('E-commerce Sicuro', 3),  -- UI/UX Designer
('E-commerce Sicuro', 7),  -- Frontend Developer

-- Progetto: App Fitness AI
('App Fitness AI', 1),  -- Esperto AI
('App Fitness AI', 6),  -- DevOps Specialist
('App Fitness AI', 8),  -- Backend Developer

-- Progetto: Social Learning Platform
('Social Learning Platform', 3),  -- UI/UX Designer
('Social Learning Platform', 7),  -- Frontend Developer
('Social Learning Platform', 8),  -- Backend Developer

-- Progetto: VR Therapy
('VR Therapy', 1),  -- Esperto AI
('VR Therapy', 4),  -- Cybersecurity Analyst
('VR Therapy', 10), -- Blockchain Developer

-- Progetto: E-Payment Blockchain
('E-Payment Blockchain', 10), -- Blockchain Developer
('E-Payment Blockchain', 2),  -- Sviluppatore Backend
('E-Payment Blockchain', 9);  -- Network Administrator
 

INSERT INTO ProfiloSkill (id_Profilo, nome_Competenza, livello) VALUES
-- Esperto AI
(1, 'AI', 5),
(1, 'Machine Learning', 5),
(1, 'Big Data', 4),

-- Sviluppatore Backend
(2, 'Java', 5),
(2, 'PHP', 4),


-- UI/UX Designer
(3, 'HTML', 5),
(3, 'ReactJS', 4),
(3, 'UX Design', 5),

-- Cybersecurity Analyst
(4, 'Cybersecurity', 5),
(4, 'Networking', 4),

-- Data Scientist
(5, 'Python', 5),
(5, 'Machine Learning', 4),
(5, 'Big Data', 5),

-- Full Stack Developer
(6, 'Java', 5),
(6, 'PHP', 5),
(6, 'ReactJS', 4),
(6, 'HTML', 5),

-- Cloud Engineer
(7, 'Networking', 4),
(7, 'Cybersecurity', 3),
(7, 'Big Data', 4),

-- DevOps Specialist
(8, 'Networking', 5),
(8, 'Python', 4),
(8, 'AI', 3),

-- Blockchain Developer
(9, 'Blockchain', 5),
(9, 'Cybersecurity', 4),
(9, 'Networking', 3);
  
INSERT INTO Componente (nome, descrizione, prezzo)
VALUES 
  ('Raspberry Pi 4', 'Microcomputer con 4GB RAM, USB-C, HDMI', 59.99),
  ('Arduino UNO', 'Scheda microcontrollore basata su ATmega328P', 24.50),
  ('Modulo WiFi ESP8266', 'Modulo wireless per connessione WiFi', 5.00),
  ('Sensore DHT11', 'Sensore di temperatura e umidità', 2.80),
  ('Modulo Relè 5V', 'Modulo a relè per controllare dispositivi ad alto voltaggio', 3.20),
  ('Display OLED 0.96"', 'Display grafico 128x64 pixel I2C', 6.75),
  ('Breadboard', 'Scheda per prototipazione rapida senza saldature', 4.00),
  ('Cavi Jumper M-M', 'Set di 40 cavi per collegamenti su breadboard', 2.00),
  ('Modulo GPS NEO-6M', 'Ricevitore GPS per progetti embedded', 12.90),
  ('Batteria Li-ion 18650', 'Batteria ricaricabile 3.7V 2600mAh', 7.00);


INSERT INTO Candidatura (email_Utente, id_Profilo, stato, nome_Progetto) VALUES
-- Paolo Gialli → Backend per E-commerce Sicuro
('paolo.gialli@email.com', 2, 'in attesa', 'E-commerce Sicuro'),

-- Lucia Bianchi → UI/UX Designer per Social Learning Platform
('lucia.bianchi@email.com', 3, 'in attesa', 'Social Learning Platform'),

-- Mario Rossi → DevOps Specialist per App Fitness AI
('mario.rossi@email.com', 8, 'in attesa', 'App Fitness AI'),

-- Giovanni Verdi → Esperto AI per VR Therapy
('giovanni.verdi@email.com', 1, 'in attesa', 'VR Therapy'),

-- Lucia Bianchi → Blockchain Developer per E-Payment Blockchain
('lucia.bianchi@email.com', 10, 'in attesa', 'E-Payment Blockchain'),

-- Paolo Gialli → UI/UX Designer per App Fitness AI
('paolo.gialli@email.com', 3, 'in attesa', 'App Fitness AI');









