<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('dropdown_options', function (Blueprint $table) {
            $table->id();
            $table->string('category', 50)->index(); // urgent_requested_by, task_type, platform, priority, status
            $table->string('label', 100);
            $table->string('value', 100);
            $table->string('group_name', 100)->nullable();
            $table->string('icon', 100)->nullable();
            $table->string('color', 30)->nullable();
            $table->integer('order_index')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['category', 'is_active', 'order_index']);
            $table->unique(['category', 'value']);
        });

        // Seed initial default options
        $now = now();
        $defaults = [
            // 1. Urgent Task Requesters
            ['category' => 'urgent_requested_by', 'label' => 'Admin', 'value' => 'Admin', 'group_name' => null, 'icon' => 'fa-solid fa-user-shield', 'color' => '#6366F1', 'order_index' => 1],
            ['category' => 'urgent_requested_by', 'label' => 'Client', 'value' => 'Client', 'group_name' => null, 'icon' => 'fa-solid fa-building', 'color' => '#3B82F6', 'order_index' => 2],
            ['category' => 'urgent_requested_by', 'label' => 'Strategist', 'value' => 'Strategist', 'group_name' => null, 'icon' => 'fa-solid fa-chess', 'color' => '#8B5CF6', 'order_index' => 3],
            ['category' => 'urgent_requested_by', 'label' => 'Other', 'value' => 'Other', 'group_name' => null, 'icon' => 'fa-solid fa-circle-question', 'color' => '#64748B', 'order_index' => 4],

            // 2. Task Types (Social Media)
            ['category' => 'task_type', 'label' => 'Reel', 'value' => 'reel', 'group_name' => 'Social Media', 'icon' => 'fa-solid fa-clapperboard', 'color' => '#EC4899', 'order_index' => 1],
            ['category' => 'task_type', 'label' => 'Post', 'value' => 'post', 'group_name' => 'Social Media', 'icon' => 'fa-solid fa-image', 'color' => '#3B82F6', 'order_index' => 2],
            ['category' => 'task_type', 'label' => 'Story', 'value' => 'story', 'group_name' => 'Social Media', 'icon' => 'fa-solid fa-mobile-screen', 'color' => '#F59E0B', 'order_index' => 3],
            ['category' => 'task_type', 'label' => 'Video', 'value' => 'video', 'group_name' => 'Social Media', 'icon' => 'fa-solid fa-video', 'color' => '#8B5CF6', 'order_index' => 4],
            ['category' => 'task_type', 'label' => 'Carousel', 'value' => 'carousel', 'group_name' => 'Social Media', 'icon' => 'fa-solid fa-layer-group', 'color' => '#10B981', 'order_index' => 5],

            // 2. Task Types (Design Work)
            ['category' => 'task_type', 'label' => 'Logo Design', 'value' => 'logo', 'group_name' => 'Design Work', 'icon' => 'fa-solid fa-pen-nib', 'color' => '#6366F1', 'order_index' => 6],
            ['category' => 'task_type', 'label' => 'Product Design / UI-UX', 'value' => 'product_design', 'group_name' => 'Design Work', 'icon' => 'fa-solid fa-compass-drafting', 'color' => '#06B6D4', 'order_index' => 7],
            ['category' => 'task_type', 'label' => 'Branding & Identity', 'value' => 'branding', 'group_name' => 'Design Work', 'icon' => 'fa-solid fa-tag', 'color' => '#F43F5E', 'order_index' => 8],
            ['category' => 'task_type', 'label' => 'Banner / Display Ad', 'value' => 'banner', 'group_name' => 'Design Work', 'icon' => 'fa-solid fa-panorama', 'color' => '#3B82F6', 'order_index' => 9],
            ['category' => 'task_type', 'label' => 'Flyer / Poster', 'value' => 'flyer', 'group_name' => 'Design Work', 'icon' => 'fa-solid fa-newspaper', 'color' => '#F59E0B', 'order_index' => 10],
            ['category' => 'task_type', 'label' => 'Brochure / Print', 'value' => 'brochure', 'group_name' => 'Design Work', 'icon' => 'fa-solid fa-book-open', 'color' => '#8B5CF6', 'order_index' => 11],

            // 2. Task Types (Web & Technical)
            ['category' => 'task_type', 'label' => 'Website Development', 'value' => 'website', 'group_name' => 'Web & Technical', 'icon' => 'fa-solid fa-globe', 'color' => '#3B82F6', 'order_index' => 12],
            ['category' => 'task_type', 'label' => 'Software / App Development', 'value' => 'software', 'group_name' => 'Web & Technical', 'icon' => 'fa-solid fa-laptop-code', 'color' => '#6366F1', 'order_index' => 13],
            ['category' => 'task_type', 'label' => 'Website Maintenance', 'value' => 'maintenance', 'group_name' => 'Web & Technical', 'icon' => 'fa-solid fa-wrench', 'color' => '#64748B', 'order_index' => 14],
            ['category' => 'task_type', 'label' => 'Server Maintenance / DevOps', 'value' => 'server_maintenance', 'group_name' => 'Web & Technical', 'icon' => 'fa-solid fa-server', 'color' => '#0EA5E9', 'order_index' => 15],

            // 2. Task Types (General)
            ['category' => 'task_type', 'label' => 'General / Other', 'value' => 'others', 'group_name' => 'General', 'icon' => 'fa-solid fa-asterisk', 'color' => '#94A3B8', 'order_index' => 16],

            // 3. Platforms
            ['category' => 'platform', 'label' => 'Instagram', 'value' => 'instagram', 'group_name' => null, 'icon' => 'fa-brands fa-instagram', 'color' => '#E1306C', 'order_index' => 1],
            ['category' => 'platform', 'label' => 'Facebook', 'value' => 'facebook', 'group_name' => null, 'icon' => 'fa-brands fa-facebook', 'color' => '#1877F2', 'order_index' => 2],
            ['category' => 'platform', 'label' => 'LinkedIn', 'value' => 'linkedin', 'group_name' => null, 'icon' => 'fa-brands fa-linkedin', 'color' => '#0A66C2', 'order_index' => 3],
            ['category' => 'platform', 'label' => 'Twitter / X', 'value' => 'twitter', 'group_name' => null, 'icon' => 'fa-brands fa-x-twitter', 'color' => '#0F172A', 'order_index' => 4],
            ['category' => 'platform', 'label' => 'WhatsApp', 'value' => 'whatsapp', 'group_name' => null, 'icon' => 'fa-brands fa-whatsapp', 'color' => '#25D366', 'order_index' => 5],
            ['category' => 'platform', 'label' => 'YouTube', 'value' => 'youtube', 'group_name' => null, 'icon' => 'fa-brands fa-youtube', 'color' => '#FF0000', 'order_index' => 6],
            ['category' => 'platform', 'label' => 'TikTok', 'value' => 'tiktok', 'group_name' => null, 'icon' => 'fa-brands fa-tiktok', 'color' => '#000000', 'order_index' => 7],

            // 4. Priorities
            ['category' => 'priority', 'label' => 'Normal', 'value' => 'normal', 'group_name' => null, 'icon' => 'fa-solid fa-circle', 'color' => '#64748B', 'order_index' => 1],
            ['category' => 'priority', 'label' => 'High', 'value' => 'high', 'group_name' => null, 'icon' => 'fa-solid fa-triangle-exclamation', 'color' => '#F59E0B', 'order_index' => 2],
            ['category' => 'priority', 'label' => 'Urgent', 'value' => 'urgent', 'group_name' => null, 'icon' => 'fa-solid fa-bolt', 'color' => '#EF4444', 'order_index' => 3],

            // 5. Statuses
            ['category' => 'status', 'label' => 'In Progress (Working now)', 'value' => 'inprogress', 'group_name' => null, 'icon' => 'fa-solid fa-bolt', 'color' => '#6366F1', 'order_index' => 1],
            ['category' => 'status', 'label' => 'To Do (Queue)', 'value' => 'todo', 'group_name' => null, 'icon' => 'fa-solid fa-list-check', 'color' => '#64748B', 'order_index' => 2],
            ['category' => 'status', 'label' => 'In Review', 'value' => 'review', 'group_name' => null, 'icon' => 'fa-solid fa-magnifying-glass', 'color' => '#F59E0B', 'order_index' => 3],
            ['category' => 'status', 'label' => 'Completed', 'value' => 'completed', 'group_name' => null, 'icon' => 'fa-solid fa-circle-check', 'color' => '#10B981', 'order_index' => 4],
        ];

        foreach ($defaults as &$item) {
            $item['is_active'] = true;
            $item['created_at'] = $now;
            $item['updated_at'] = $now;
        }

        DB::table('dropdown_options')->insert($defaults);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dropdown_options');
    }
};
