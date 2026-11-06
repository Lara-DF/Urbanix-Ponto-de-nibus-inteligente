<?php
class ServicoAviso {
    private $pdo;

    public function __construct($conexaoPdo) {
        $this->pdo = $conexaoPdo;
    }

    public function criar($titulo, $descricao, $categoria) {
        $sql = "INSERT INTO servicos_utilidade (titulo, descricao, categoria) VALUES (:titulo, :descricao, :categoria)";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            ':titulo' => $titulo,
            ':descricao' => $descricao,
            ':categoria' => $categoria
        ]);
    }

    public function listarTodos() {
        $sql = "SELECT * FROM servicos_utilidade ORDER BY id DESC";
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function buscarPorId($id) {
        $sql = "SELECT * FROM servicos_utilidade WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function atualizar($id, $titulo, $descricao, $categoria) {
        $sql = "UPDATE servicos_utilidade SET titulo = :titulo, descricao = :descricao, categoria = :categoria WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            ':id' => $id,
            ':titulo' => $titulo,
            ':descricao' => $descricao,
            ':categoria' => $categoria
        ]);
    }

    public function excluir($id) {
        $sql = "DELETE FROM servicos_utilidade WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([':id' => $id]);
    }
}
?>