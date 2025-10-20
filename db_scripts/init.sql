CREATE TABLE usuarios (
    id SERIAL PRIMARY KEY,
    usuario VARCHAR(50) NOT NULL UNIQUE,
    senha VARCHAR(255) NOT NULL
);


CREATE TABLE equipes (
    id SERIAL PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    cor VARCHAR(20) DEFAULT '#4A90E2'
);


CREATE TABLE servicos (
    id SERIAL PRIMARY KEY,
    equipe_id INT NOT NULL,
    quantidade INT NOT NULL,
    data_registro DATE NOT NULL,
    observacao TEXT,
    FOREIGN KEY (equipe_id) REFERENCES equipes(id)
);


-- sql do dashboard
CREATE OR REPLACE VIEW vw_produtividade AS
SELECT
    e.id,
    e.nome,
    e.cor,
    COALESCE(SUM(s.quantidade), 0) AS total_servicos,
    COUNT(s.id) AS registros
FROM
    equipes e
LEFT JOIN
    servicos s ON e.id = s.equipe_id
GROUP BY
    e.id, e.nome, e.cor
ORDER BY
    e.nome;

INSERT INTO equipes (nome, cor) VALUES
('Equipe Alpha', '#E74C3C'),
('Equipe Bravo', '#3498DB'),
('Equipe Charlie', '#2ECC71');