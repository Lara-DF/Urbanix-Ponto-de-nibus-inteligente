<?php
class ServicoOnibus {
    private $pdo;

    public function __construct($conexaoPdo) {
        $this->pdo = $conexaoPdo;
    }

    public function criar($numero_linha, $destino, $tempo_chegada_minutos, $status) {
        $sql = "INSERT INTO onibus_linhas (numero_linha, destino, tempo_chegada_minutos, status) 
                VALUES (:numero_linha, :destino, :tempo_chegada_minutos, :status)";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            ':numero_linha' => $numero_linha,
            ':destino' => $destino,
            ':tempo_chegada_minutos' => $tempo_chegada_minutos,
            ':status' => $status
        ]);
    }

    public function listarTodos() {
        $sql = "SELECT * FROM onibus_linhas ORDER BY id DESC";
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function buscarPorId($id) {
        $sql = "SELECT * FROM onibus_linhas WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function atualizar($id, $numero_linha, $destino, $tempo_chegada_minutos, $status) {
        $sql = "UPDATE onibus_linhas 
                SET numero_linha = :numero_linha, destino = :destino, tempo_chegada_minutos = :tempo_chegada_minutos, status = :status 
                WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            ':id' => $id,
            ':numero_linha' => $numero_linha,
            ':destino' => $destino,
            ':tempo_chegada_minutos' => $tempo_chegada_minutos,
            ':status' => $status
        ]);
    }

    public function excluir($id) {
        $sql = "DELETE FROM onibus_linhas WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([':id' => $id]);
    }
}
?>