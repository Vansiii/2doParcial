<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class EnsureStorageDirectories extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'storage:ensure';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Asegurar que existan todos los directorios de almacenamiento necesarios';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $directories = [
            storage_path('app/temp'),
            storage_path('app/public'),
            storage_path('framework/cache'),
            storage_path('framework/sessions'),
            storage_path('framework/views'),
            storage_path('logs'),
        ];

        $this->info('Verificando directorios de almacenamiento...');

        foreach ($directories as $directory) {
            if (!file_exists($directory)) {
                mkdir($directory, 0755, true);
                $this->info("✓ Creado: {$directory}");
            } else {
                $this->line("✓ Existe: {$directory}");
            }
        }

        $this->newLine();
        $this->info('✓ Todos los directorios de almacenamiento están listos.');

        return Command::SUCCESS;
    }
}
