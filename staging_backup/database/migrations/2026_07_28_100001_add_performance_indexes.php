<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Phase 3 — Database Optimization Migration
 * 
 * Adds performance indexes on high-frequency query columns:
 * - news.status, news.published_at, news.slug (search & sort)
 * - complaints.status, complaints.ticket_number (tracking)
 * - downloads.status, downloads.download_count (public listing)
 * - ppid_requests.status, ppid_requests.ticket_number (tracking)
 * - api_logs.created_at, api_logs.api_key_id (analytics)
 * - visitor_logs.visited_at, visitor_logs.ip_address (analytics)
 * - innovations.status (public listing)
 */
return new class extends Migration
{
    public function up(): void
    {
        // NEWS table indexes
        $this->addIndexIfNotExists('news', 'status', 'news_status_index');
        $this->addIndexIfNotExists('news', 'published_at', 'news_published_at_index');
        $this->addIndexIfNotExists('news', 'is_featured', 'news_is_featured_index');
        $this->addCompositeIndexIfNotExists('news', ['status', 'published_at'], 'news_status_published_index');

        // ANNOUNCEMENTS table indexes
        $this->addIndexIfNotExists('announcements', 'status', 'announcements_status_index');
        $this->addIndexIfNotExists('announcements', 'start_date', 'announcements_start_date_index');

        // COMPLAINTS table indexes
        $this->addIndexIfNotExists('complaints', 'status', 'complaints_status_index');
        $this->addIndexIfNotExists('complaints', 'created_at', 'complaints_created_at_index');
        $this->addIndexIfNotExists('complaints', 'assigned_to', 'complaints_assigned_to_index');

        // DOWNLOADS table indexes
        $this->addIndexIfNotExists('downloads', 'status', 'downloads_status_index');
        $this->addIndexIfNotExists('downloads', 'download_count', 'downloads_download_count_index');
        $this->addCompositeIndexIfNotExists('downloads', ['status', 'download_count'], 'downloads_status_count_index');

        // PPID REQUESTS table indexes
        $this->addIndexIfNotExists('ppid_requests', 'status', 'ppid_requests_status_index');
        $this->addIndexIfNotExists('ppid_requests', 'created_at', 'ppid_requests_created_at_index');

        // PPID DOCUMENTS table indexes
        $this->addIndexIfNotExists('ppid_documents', 'status', 'ppid_documents_status_index');
        $this->addIndexIfNotExists('ppid_documents', 'category', 'ppid_documents_category_index');

        // API_LOGS table indexes
        $this->addIndexIfNotExists('api_logs', 'api_key_id', 'api_logs_api_key_id_index');
        $this->addIndexIfNotExists('api_logs', 'created_at', 'api_logs_created_at_index');
        $this->addCompositeIndexIfNotExists('api_logs', ['api_key_id', 'created_at'], 'api_logs_key_date_index');

        // VISITOR_LOGS table indexes
        $this->addIndexIfNotExists('visitor_logs', 'visited_at', 'visitor_logs_visited_at_index');
        $this->addIndexIfNotExists('visitor_logs', 'ip_address', 'visitor_logs_ip_address_index');

        // INNOVATIONS table indexes
        $this->addIndexIfNotExists('innovations', 'status', 'innovations_status_index');
        $this->addIndexIfNotExists('innovations', 'created_at', 'innovations_created_at_index');

        // GALLERY_ITEMS table indexes
        $this->addIndexIfNotExists('gallery_items', 'gallery_album_id', 'gallery_items_album_id_index');

        // BANNERS table indexes
        $this->addIndexIfNotExists('banners', 'is_active', 'banners_is_active_index');
        $this->addIndexIfNotExists('banners', 'sort_order', 'banners_sort_order_index');

        // DEMOGRAPHIC_DATASETS table indexes
        $this->addIndexIfNotExists('demographic_datasets', 'status', 'demographic_datasets_status_index');
        $this->addCompositeIndexIfNotExists('demographic_datasets', ['year', 'semester', 'type'], 'demographic_year_sem_type_index');

        // SURVEY_RESPONSES table indexes
        $this->addIndexIfNotExists('survey_responses', 'survey_period_id', 'survey_responses_period_id_index');
        $this->addIndexIfNotExists('survey_responses', 'created_at', 'survey_responses_created_at_index');
    }

    public function down(): void
    {
        // Reverse all indexes
        $indexesToRemove = [
            ['news', 'news_status_index'],
            ['news', 'news_published_at_index'],
            ['news', 'news_is_featured_index'],
            ['news', 'news_status_published_index'],
            ['announcements', 'announcements_status_index'],
            ['announcements', 'announcements_start_date_index'],
            ['complaints', 'complaints_status_index'],
            ['complaints', 'complaints_created_at_index'],
            ['complaints', 'complaints_assigned_to_index'],
            ['downloads', 'downloads_status_index'],
            ['downloads', 'downloads_download_count_index'],
            ['downloads', 'downloads_status_count_index'],
            ['ppid_requests', 'ppid_requests_status_index'],
            ['ppid_requests', 'ppid_requests_created_at_index'],
            ['ppid_documents', 'ppid_documents_status_index'],
            ['ppid_documents', 'ppid_documents_category_index'],
            ['api_logs', 'api_logs_api_key_id_index'],
            ['api_logs', 'api_logs_created_at_index'],
            ['api_logs', 'api_logs_key_date_index'],
            ['visitor_logs', 'visitor_logs_visited_at_index'],
            ['visitor_logs', 'visitor_logs_ip_address_index'],
            ['innovations', 'innovations_status_index'],
            ['innovations', 'innovations_created_at_index'],
            ['gallery_items', 'gallery_items_album_id_index'],
            ['banners', 'banners_is_active_index'],
            ['banners', 'banners_sort_order_index'],
            ['demographic_datasets', 'demographic_datasets_status_index'],
            ['demographic_datasets', 'demographic_year_sem_type_index'],
            ['survey_responses', 'survey_responses_period_id_index'],
            ['survey_responses', 'survey_responses_created_at_index'],
        ];

        foreach ($indexesToRemove as [$table, $index]) {
            $this->dropIndexIfExists($table, $index);
        }
    }

    private function addIndexIfNotExists(string $table, string $column, string $indexName): void
    {
        try {
            if (Schema::hasColumn($table, $column) && !$this->indexExists($table, $indexName)) {
                Schema::table($table, function (Blueprint $table) use ($column, $indexName) {
                    $table->index($column, $indexName);
                });
            }
        } catch (\Exception $e) {
            // Index might already exist — skip silently
        }
    }

    private function addCompositeIndexIfNotExists(string $table, array $columns, string $indexName): void
    {
        try {
            if (!$this->indexExists($table, $indexName)) {
                Schema::table($table, function (Blueprint $t) use ($columns, $indexName) {
                    $t->index($columns, $indexName);
                });
            }
        } catch (\Exception $e) {
            // Skip silently if columns don't exist
        }
    }

    private function dropIndexIfExists(string $table, string $indexName): void
    {
        try {
            if ($this->indexExists($table, $indexName)) {
                Schema::table($table, function (Blueprint $t) use ($indexName) {
                    $t->dropIndex($indexName);
                });
            }
        } catch (\Exception $e) {
            // Skip silently
        }
    }

    private function indexExists(string $table, string $indexName): bool
    {
        try {
            $indexes = DB::select("SHOW INDEX FROM `{$table}` WHERE Key_name = ?", [$indexName]);
            return count($indexes) > 0;
        } catch (\Exception $e) {
            return false;
        }
    }
};
