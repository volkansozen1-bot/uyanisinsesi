<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Env;
use App\Core\Migrator;

/**
 * Yayin sonrasi veritabani guncellemesi. GitHub Actions FTP yuklemesinden sonra cagirir:
 *   POST /_deploy/migrate   Basligi: X-Deploy-Token: <.env'deki DEPLOY_TOKEN>
 * DEPLOY_TOKEN tanimli degilse ya da 32 karakterden kisaysa adres yokmus gibi 404 doner.
 */
final class DeployController
{
    public function hidden(): void
    {
        \App\Core\Router::notFound();
    }

    public function migrate(): void
    {
        $token = (string) Env::get('DEPLOY_TOKEN', '');
        $sent  = (string) ($_SERVER['HTTP_X_DEPLOY_TOKEN'] ?? '');

        if (strlen($token) < 32 || !hash_equals($token, $sent)) {
            http_response_code(404);
            exit;
        }

        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-store');
        $result = Migrator::run();
        if ($result['error'] !== null) {
            http_response_code(500);
        }
        echo json_encode($result, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }
}
