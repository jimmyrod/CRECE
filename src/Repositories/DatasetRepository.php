<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\App;
use App\Support\Text;
use PDO;

class DatasetRepository
{
    public function allVisible(?array $user = null): array
    {
        $pdo = App::db();
        if ($user && in_array($user['role'], ['administrator', 'reviewer', 'internal_researcher'], true)) {
            $stmt = $pdo->query('SELECT * FROM datasets ORDER BY created_at DESC');
            return $stmt->fetchAll();
        }

        $stmt = $pdo->prepare('SELECT * FROM datasets WHERE visibility = :visibility OR visibility = :public ORDER BY created_at DESC');
        $stmt->execute([
            'visibility' => $user ? 'restricted' : 'public',
            'public' => 'public',
        ]);
        return $stmt->fetchAll();
    }

    public function findBySlug(string $slug): ?array
    {
        $stmt = App::db()->prepare('SELECT * FROM datasets WHERE slug = :slug LIMIT 1');
        $stmt->execute(['slug' => $slug]);
        $dataset = $stmt->fetch();
        return $dataset ?: null;
    }

    public function create(array $data): array
    {
        $slug = Text::slug($data['title']);
        $stmt = App::db()->prepare('INSERT INTO datasets (slug, title, summary, keywords, category, geographic_scope, publication_year, contact_name, contact_email, legal_restrictions, visibility, storage_uri, default_access_level, created_by)
            VALUES (:slug, :title, :summary, :keywords, :category, :geographic_scope, :publication_year, :contact_name, :contact_email, :legal_restrictions, :visibility, :storage_uri, :default_access_level, :created_by)');
        $stmt->execute([
            'slug' => $slug,
            'title' => $data['title'],
            'summary' => $data['summary'],
            'keywords' => $data['keywords'] ?? null,
            'category' => $data['category'] ?? null,
            'geographic_scope' => $data['geographic_scope'] ?? null,
            'publication_year' => $data['publication_year'] ?: null,
            'contact_name' => $data['contact_name'] ?? null,
            'contact_email' => $data['contact_email'] ?? null,
            'legal_restrictions' => $data['legal_restrictions'] ?? null,
            'visibility' => $data['visibility'] ?? 'restricted',
            'storage_uri' => $data['storage_uri'],
            'default_access_level' => $data['default_access_level'] ?? 'preview',
            'created_by' => $data['created_by'],
        ]);

        return $this->findBySlug($slug) ?? [];
    }

    public function createVersion(array $data): int
    {
        $stmt = App::db()->prepare('INSERT INTO dataset_versions (dataset_id, version_label, file_name, file_format, file_size_bytes, checksum, storage_uri, change_log, uploaded_by)
            VALUES (:dataset_id, :version_label, :file_name, :file_format, :file_size_bytes, :checksum, :storage_uri, :change_log, :uploaded_by)');
        $stmt->execute([
            'dataset_id' => $data['dataset_id'],
            'version_label' => $data['version_label'],
            'file_name' => $data['file_name'],
            'file_format' => $data['file_format'],
            'file_size_bytes' => $data['file_size_bytes'],
            'checksum' => $data['checksum'],
            'storage_uri' => $data['storage_uri'],
            'change_log' => $data['change_log'] ?? null,
            'uploaded_by' => $data['uploaded_by'],
        ]);

        return (int) App::db()->lastInsertId();
    }

    public function versionsForDataset(int $datasetId): array
    {
        $stmt = App::db()->prepare('SELECT * FROM dataset_versions WHERE dataset_id = :dataset_id ORDER BY uploaded_at DESC');
        $stmt->execute(['dataset_id' => $datasetId]);
        return $stmt->fetchAll();
    }

    public function findVersion(int $id): ?array
    {
        $stmt = App::db()->prepare('SELECT dv.*, d.slug AS dataset_slug, d.visibility, d.default_access_level FROM dataset_versions dv JOIN datasets d ON dv.dataset_id = d.id WHERE dv.id = :id');
        $stmt->execute(['id' => $id]);
        $version = $stmt->fetch();
        return $version ?: null;
    }
}
