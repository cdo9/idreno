<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Client;
use App\Utils\PhoneFormatter;
use Illuminate\Support\Facades\Log;

class ConvertPhoneNumbers extends Command {
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'phone:convert {--dry-run : Show what would be converted without making changes}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Convert all phone numbers to E164 format';

    /**
     * Execute the console command.
     */
    public function handle() {
        $isDryRun = $this->option('dry-run');

        if ($isDryRun) {
            $this->info('🔍 Mode simulation - Aucune modification ne sera effectuée');
        } else {
            $this->info('⚡ Mode conversion - Les numéros vont être modifiés');
        }

        $clients = Client::whereNotNull('telephone1')
            ->orWhereNotNull('telephone2')
            ->get();

        $this->info("📱 Trouvé {$clients->count()} clients avec des numéros de téléphone");

        $converted = 0;
        $errors = 0;

        foreach ($clients as $client) {
            $this->processClient($client, $isDryRun, $converted, $errors);
        }

        $this->info("✅ Conversion terminée !");
        $this->info("📊 Statistiques :");
        $this->info("   - Numéros convertis : {$converted}");
        $this->info("   - Erreurs : {$errors}");

        if ($isDryRun) {
            $this->warn("⚠️  Aucune modification n'a été effectuée (mode simulation)");
            $this->info("💡 Exécutez sans --dry-run pour appliquer les changements");
        }
    }

    private function processClient($client, $isDryRun, &$converted, &$errors) {
        $changes = [];

        if ($client->telephone1) {
            $result = $this->convertPhone($client->telephone1, 'telephone1');
            if ($result['success']) {
                $changes['telephone1'] = $result['e164'];
                $converted++;
            } else {
                $errors++;
                $this->error("❌ Client {$client->id} - telephone1: {$result['error']}");
            }
        }

        if ($client->telephone2) {
            $result = $this->convertPhone($client->telephone2, 'telephone2');
            if ($result['success']) {
                $changes['telephone2'] = $result['e164'];
                $converted++;
            } else {
                $errors++;
                $this->error("❌ Client {$client->id} - telephone2: {$result['error']}");
            }
        }

        if (!empty($changes)) {
            if ($isDryRun) {
                $this->line("🔄 Client {$client->id} :");
                foreach ($changes as $field => $newValue) {
                    $oldValue = $client->$field;
                    $this->line("   {$field}: '{$oldValue}' → '{$newValue}'");
                }
            } else {
                try {
                    // On applique les nouvelles infos
                    foreach ($changes as $field => $newValue) {
                        $client->$field = $newValue;
                    }
                    
                    $saved = $client->save();
                    
                    if ($saved) {
                        // Vérifier que les changements sont bien persistés
                        $client->refresh();
                        $this->info("✅ Client {$client->id} mis à jour avec succès");
                    } else {
                        $errors++;
                        $this->error("❌ Échec de la sauvegarde pour le client {$client->id}");
                    }
                } catch (\Exception $e) {
                    $errors++;
                    $this->error("❌ Erreur lors de la mise à jour du client {$client->id}: {$e->getMessage()}");
                }
            }
        }
    }

    private function convertPhone($phone, $fieldName) {
        try {
            $e164 = PhoneFormatter::formatForStorage($phone);
            
            if ($e164) {
                return [
                    'success' => true,
                    'e164' => $e164,
                    'original' => $phone
                ];
            }
            
            return [
                'success' => false,
                'error' => "Impossible de parser '{$phone}'"
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => "Erreur lors de la validation du numéro '{$phone}': {$e->getMessage()}"
            ];
        }
    }
}
