<?php

namespace Database\Seeders;

use App\Services\MenuImporter;
use Illuminate\Database\Seeder;

class MenuSeeder extends Seeder
{
    /**
     * One-time bootstrap of the canonical menu from the bundled menu.json
     * snapshot. After this, genz-admin is the source of truth — edits happen
     * through the admin API, not by re-seeding.
     */
    public function run(): void
    {
        $path = database_path('data/menu.json');
        $menu = json_decode(file_get_contents($path), true);

        $result = app(MenuImporter::class)->import($menu);

        $this->command?->info(
            "Menu imported: {$result['categories']} categories, {$result['items']} items."
        );
    }
}
