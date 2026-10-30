<?php

namespace Tests\Feature;

use App\Exports\AuditoriaLogsExport;
use App\Models\AuditoriaLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use Tests\TestCase;

class AdminLogTest extends TestCase
{
    use RefreshDatabase;

    private function criarLog(array $atributos = []): AuditoriaLog
    {
        return AuditoriaLog::forceCreate(array_merge([
            'user_id' => null,
            'modulo' => 'Livros',
            'objeto_id' => '10',
            'alteracao' => 'Criou o livro Exemplo',
            'ip' => '192.168.1.10',
            'browser' => 'Google Chrome',
            'created_at' => now(),
        ], $atributos));
    }

    public function test_visitante_e_cidadao_comum_nao_podem_aceder_aos_logs(): void
    {
        $this->get(route('admin.logs.index'))
            ->assertRedirect(route('login'));

        $this->get(route('admin.logs.export'))
            ->assertRedirect(route('login'));

        $cidadao = User::factory()->create(['role' => 'cidadao']);

        $this->actingAs($cidadao)
            ->get(route('admin.logs.index'))
            ->assertStatus(403);

        $this->actingAs($cidadao)
            ->get(route('admin.logs.export'))
            ->assertStatus(403);

        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->get(route('admin.logs.index'))
            ->assertOk();
    }

    public function test_administrador_pode_visualizar_listagem_de_logs_com_os_oito_campos(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $leitor = User::factory()->create(['name' => 'Mariana Fonseca', 'email' => 'mariana@exemplo.pt', 'role' => 'cidadao']);

        $dataEspecifica = Carbon::create(2026, 10, 29, 14, 30, 15);

        $this->criarLog([
            'user_id' => $leitor->id,
            'modulo' => 'Requisições',
            'objeto_id' => '123',
            'alteracao' => 'Criou a requisição do livro Os Maias',
            'ip' => '192.168.1.50',
            'browser' => 'Mozilla Firefox',
            'created_at' => $dataEspecifica,
        ]);

        $this->actingAs($admin)
            ->get(route('admin.logs.index'))
            ->assertOk()
            ->assertSee('Logs de Auditoria')
            ->assertSee('29/10/2026')
            ->assertSee('14:30:15')
            ->assertSee('Mariana Fonseca')
            ->assertSee('mariana@exemplo.pt')
            ->assertSee('Requisições')
            ->assertSee('#123')
            ->assertSee('Criou a requisição do livro Os Maias')
            ->assertSee('192.168.1.50')
            ->assertSee('Mozilla Firefox');
    }

    public function test_filtro_por_modulo_filtra_registos_corretamente(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $logLivro = $this->criarLog(['modulo' => 'Livros', 'alteracao' => 'Ação do catálogo']);
        $logRequisicao = $this->criarLog(['modulo' => 'Requisições', 'alteracao' => 'Ação do empréstimo']);

        $resposta = $this->actingAs($admin)
            ->get(route('admin.logs.index', ['modulo' => 'Livros']));

        $resposta->assertOk();
        $resposta->assertSee('Ação do catálogo');
        $resposta->assertDontSee('Ação do empréstimo');
    }

    public function test_filtro_por_utilizador_filtra_registos_corretamente(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $userA = User::factory()->create(['name' => 'Utilizador Alfa', 'role' => 'cidadao']);
        $userB = User::factory()->create(['name' => 'Utilizador Beta', 'role' => 'cidadao']);

        $this->criarLog(['user_id' => $userA->id, 'alteracao' => 'Ação do Alfa']);
        $this->criarLog(['user_id' => $userB->id, 'alteracao' => 'Ação do Beta']);

        $resposta = $this->actingAs($admin)
            ->get(route('admin.logs.index', ['user_id' => $userA->id]));

        $resposta->assertOk();
        $resposta->assertSee('Ação do Alfa');
        $resposta->assertDontSee('Ação do Beta');
    }

    public function test_filtro_por_intervalo_de_datas_filtra_registos_corretamente(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->criarLog([
            'alteracao' => 'Ação Antiga Fora',
            'created_at' => Carbon::create(2026, 10, 1, 10, 0, 0),
        ]);

        $this->criarLog([
            'alteracao' => 'Ação Elegível Dentro',
            'created_at' => Carbon::create(2026, 10, 15, 12, 0, 0),
        ]);

        $this->criarLog([
            'alteracao' => 'Ação Futura Fora',
            'created_at' => Carbon::create(2026, 10, 28, 18, 0, 0),
        ]);

        $resposta = $this->actingAs($admin)->get(route('admin.logs.index', [
            'data_inicio' => '2026-10-10',
            'data_fim' => '2026-10-20',
        ]));

        $resposta->assertOk();
        $resposta->assertSee('Ação Elegível Dentro');
        $resposta->assertDontSee('Ação Antiga Fora');
        $resposta->assertDontSee('Ação Futura Fora');
    }

    public function test_pesquisa_livre_procura_em_acao_objeto_ip_e_utilizador(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $cidadao = User::factory()->create(['name' => 'Alexandre Herculano', 'email' => 'herculano@exemplo.pt', 'role' => 'cidadao']);

        $logAcao = $this->criarLog(['alteracao' => 'Operação Especial XPTO']);
        $logObjeto = $this->criarLog(['objeto_id' => 'ENC-SPEC-999', 'alteracao' => 'Outra ação qualquer']);
        $logIp = $this->criarLog(['ip' => '10.20.30.40', 'alteracao' => 'Acesso por IP fixo']);
        $logUser = $this->criarLog(['user_id' => $cidadao->id, 'alteracao' => 'Ação do autor clássico']);

        $buscaAcao = $this->actingAs($admin)->get(route('admin.logs.index', ['search' => 'XPTO']));
        $buscaAcao->assertSee('Operação Especial XPTO')->assertDontSee('ENC-SPEC-999');

        $buscaObjeto = $this->actingAs($admin)->get(route('admin.logs.index', ['search' => 'SPEC-999']));
        $buscaObjeto->assertSee('ENC-SPEC-999')->assertDontSee('10.20.30.40');

        $buscaIp = $this->actingAs($admin)->get(route('admin.logs.index', ['search' => '10.20.30.40']));
        $buscaIp->assertSee('10.20.30.40')->assertDontSee('Operação Especial XPTO');

        $buscaNome = $this->actingAs($admin)->get(route('admin.logs.index', ['search' => 'Herculano']));
        $buscaNome->assertSee('Alexandre Herculano')->assertDontSee('10.20.30.40');

        $buscaEmail = $this->actingAs($admin)->get(route('admin.logs.index', ['search' => 'herculano@exemplo.pt']));
        $buscaEmail->assertSee('Alexandre Herculano')->assertDontSee('Operação Especial XPTO');
    }

    public function test_administrador_pode_exportar_logs_em_excel_e_csv_respeitando_filtros(): void
    {
        Excel::fake();
        Excel::matchByRegex();

        $admin = User::factory()->create(['role' => 'admin']);

        $this->criarLog(['modulo' => 'Livros', 'alteracao' => 'Livro Exportável']);
        $this->criarLog(['modulo' => 'Requisições', 'alteracao' => 'Requisição Não Filtrada']);

        $this->actingAs($admin)
            ->get(route('admin.logs.export', ['formato' => 'xlsx', 'modulo' => 'Livros']))
            ->assertOk();

        Excel::assertDownloaded('/^auditoria_logs_.*\.xlsx$/');

        $this->actingAs($admin)
            ->get(route('admin.logs.export', ['formato' => 'csv']))
            ->assertOk();

        Excel::assertDownloaded('/^auditoria_logs_.*\.csv$/');
    }
}