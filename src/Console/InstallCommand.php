<?php

namespace SpyApp\ThemeInverness\Console;

use Filament\Commands\MakeThemeCommand;
use Filament\Facades\Filament;
use Filament\Panel;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

use function Laravel\Prompts\select;

class InstallCommand extends Command
{
    protected $signature = 'inverness:install';

    protected $description = 'Install the Inverness theme for a Filament panel';

    protected string $importStatement = "@import '../../../../vendor/spykapps/theme-inverness/resources/css/inverness.css';";

    public function handle(): void
    {
        $this->newLine();
        $this->line('  🏴 <fg=red;options=bold>Inverness</> — Premium Filament Theme by <fg=gray>SpyKapps</>');
        $this->line('  <fg=gray>─────────────────────────────────────────</>');
        $this->newLine();

        $panels = Filament::getPanels();

        if (empty($panels)) {
            $this->newLine();
            $this->line('  ❌ <fg=red>No Filament panels found.</>');
            $this->line('     Create a panel first, then run this command again.');
            $this->newLine();

            return;
        }

        $panelId = count($panels) > 1
            ? select(
                label: 'Which panel would you like to install the theme for?',
                options: array_map(
                    fn (Panel $panel): string => $panel->getId(),
                    $panels,
                ),
            )
            : array_key_first($panels);

        $themeCssPath = resource_path("css/filament/{$panelId}/theme.css");

        // If the theme CSS file doesn't exist yet, run the Filament make:theme command
        if (! File::exists($themeCssPath)) {
            $this->line("  ⚙️  Creating theme stylesheet for <fg=yellow>{$panelId}</> panel...");
            $this->newLine();

            $this->call(MakeThemeCommand::class, ['panel' => $panelId]);
        }

        if (! File::exists($themeCssPath)) {
            $this->newLine();
            $this->line('  ❌ <fg=red>Could not find or create the theme file.</>');
            $this->line("     Expected path: <fg=gray>{$themeCssPath}</>");
            $this->newLine();

            return;
        }

        $themeCss = File::get($themeCssPath);

        // Check if already installed
        if (str_contains($themeCss, $this->importStatement)) {
            $this->newLine();
            $this->line('  ✅ <fg=green>Inverness is already installed</> for the <fg=yellow>' . $panelId . '</> panel.');
            $this->line('     No changes were made.');
            $this->newLine();

            return;
        }

        // Try to find the Filament base theme import and insert after it
        $filamentImport = "@import '../../../../vendor/filament/filament/resources/css/theme.css';";

        if (str_contains($themeCss, $filamentImport)) {
            File::replaceInFile(
                $filamentImport,
                $filamentImport . "\n" . $this->importStatement,
                $themeCssPath,
            );
        } else {
            // Existing theme file without standard Filament import — append at the end
            File::append($themeCssPath, "\n" . $this->importStatement . "\n");

            $this->newLine();
            $this->line('  ⚠️  <fg=yellow>Could not find the standard Filament theme import.</>');
            $this->line('     The Inverness import was appended to the end of your theme file.');
            $this->line("     Please review: <fg=gray>{$themeCssPath}</>");
            $this->line('     Ensure the Inverness import comes <fg=yellow>after</> the Filament base import.');
            $this->newLine();
        }

        $this->line('  ✅ <fg=green>Theme stylesheet added successfully!</>');
        $this->newLine();
        $this->line('  <fg=gray>─────────────────────────────────────────</>');
        $this->newLine();
        $this->line('  📋 <options=bold>Next steps:</>');
        $this->newLine();
        $this->line('     <fg=yellow>1.</> Register the plugin in your <fg=cyan>' . $panelId . '</> panel provider:');
        $this->newLine();
        $this->line('        <fg=green>->plugin(\SpyApp\ThemeInverness\ThemeInvernessPlugin::make())</>');
        $this->newLine();
        $this->line('     <fg=yellow>2.</> Compile your assets:');
        $this->newLine();
        $this->line('        <fg=green>npm run build</>');
        $this->newLine();
        $this->line('  <fg=gray>─────────────────────────────────────────</>');
        $this->line('  🎉 <fg=gray>Thank you for choosing Inverness by</> <fg=red>SpyKapps</>');
        $this->line('  <fg=gray>─────────────────────────────────────────</>');
        $this->newLine();
    }
}