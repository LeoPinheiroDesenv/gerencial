<?php

namespace App\Http\Controllers;

use App\Models\NFe;
use App\Models\Pedido;
use App\Services\TecnospeedService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class NFeController extends Controller
{
    protected $tecnospeedService;

    public function __construct(TecnospeedService $tecnospeedService)
    {
        $this->tecnospeedService = $tecnospeedService;
    }

    public function emitir(Request $request, Pedido $pedido)
    {
        try {
            $this->tecnospeedService->initialize($pedido->empresa_id);
            $result = $this->tecnospeedService->emitirNFe($pedido);

            return response()->json([
                'success' => true,
                'message' => 'NFe emitida com sucesso',
                'data' => $result
            ]);

        } catch (\Exception $e) {
            Log::error('Erro ao emitir NFe: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erro ao emitir NFe: ' . $e->getMessage()
            ], 500);
        }
    }

    public function consultarStatus(NFe $nfe)
    {
        try {
            $this->tecnospeedService->initialize($nfe->empresa_id);
            $result = $this->tecnospeedService->consultarStatusNFe($nfe->numero);

            // Atualiza o status da NFe
            $nfe->update([
                'status' => $result['status'] ?? $nfe->status,
                'erro' => $result['erro'] ?? null
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Status da NFe consultado com sucesso',
                'data' => $result
            ]);

        } catch (\Exception $e) {
            Log::error('Erro ao consultar status da NFe: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erro ao consultar status da NFe: ' . $e->getMessage()
            ], 500);
        }
    }

    public function cancelar(Request $request, NFe $nfe)
    {
        try {
            $request->validate([
                'justificativa' => 'required|string|min:15|max:255'
            ]);

            $this->tecnospeedService->initialize($nfe->empresa_id);
            $result = $this->tecnospeedService->cancelarNFe($nfe->numero, $request->justificativa);

            return response()->json([
                'success' => true,
                'message' => 'NFe cancelada com sucesso',
                'data' => $result
            ]);

        } catch (\Exception $e) {
            Log::error('Erro ao cancelar NFe: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erro ao cancelar NFe: ' . $e->getMessage()
            ], 500);
        }
    }

    public function downloadXml(NFe $nfe)
    {
        try {
            if (!$nfe->xml) {
                return response()->json([
                    'success' => false,
                    'message' => 'XML não disponível para esta NFe'
                ], 404);
            }

            return response($nfe->xml, 200, [
                'Content-Type' => 'application/xml',
                'Content-Disposition' => 'attachment; filename="nfe-' . $nfe->numero . '.xml"'
            ]);

        } catch (\Exception $e) {
            Log::error('Erro ao baixar XML da NFe: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erro ao baixar XML da NFe: ' . $e->getMessage()
            ], 500);
        }
    }
} 