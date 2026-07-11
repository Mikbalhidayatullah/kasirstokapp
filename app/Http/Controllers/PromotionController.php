<?php

namespace App\Http\Controllers;

use App\Models\PointReward;
use App\Models\Promotion;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PromotionController extends Controller
{
    public function index(): View
    {
        $promotions = Promotion::query()
            ->withCount('sales')
            ->latest()
            ->get();

        $pointRewards = PointReward::query()
            ->withCount('sales')
            ->orderBy('points_cost')
            ->get();

        return view('promotions.index', compact('promotions', 'pointRewards'));
    }

    public function storePromotion(Request $request): RedirectResponse
    {
        Promotion::query()->create($this->promotionData($request, true));

        return back()->with('status', 'Promo berhasil ditambahkan.');
    }

    public function updatePromotion(Request $request, Promotion $promotion): RedirectResponse
    {
        $promotion->update($this->promotionData($request));

        return back()->with('status', 'Promo berhasil diperbarui.');
    }

    public function destroyPromotion(Promotion $promotion): RedirectResponse
    {
        if ($promotion->sales()->exists()) {
            return back()->with('error', 'Promo sudah dipakai transaksi. Nonaktifkan saja agar histori tetap aman.');
        }

        $promotion->delete();

        return back()->with('status', 'Promo berhasil dihapus.');
    }

    public function storeReward(Request $request): RedirectResponse
    {
        PointReward::query()->create($this->rewardData($request, true));

        return back()->with('status', 'Reward poin berhasil ditambahkan.');
    }

    public function updateReward(Request $request, PointReward $pointReward): RedirectResponse
    {
        $pointReward->update($this->rewardData($request));

        return back()->with('status', 'Reward poin berhasil diperbarui.');
    }

    public function destroyReward(PointReward $pointReward): RedirectResponse
    {
        if ($pointReward->sales()->exists()) {
            return back()->with('error', 'Reward sudah dipakai transaksi. Nonaktifkan saja agar histori tetap aman.');
        }

        $pointReward->delete();

        return back()->with('status', 'Reward poin berhasil dihapus.');
    }

    private function promotionData(Request $request, bool $defaultActive = false): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'discount_type' => ['required', Rule::in(['percentage', 'fixed'])],
            'discount_value' => ['required', 'numeric', 'min:0.01'],
            'member_only' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        return [
            ...$data,
            'member_only' => $request->boolean('member_only'),
            'is_active' => $request->boolean('is_active', $defaultActive),
        ];
    }

    private function rewardData(Request $request, bool $defaultActive = false): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'points_cost' => ['required', 'integer', 'min:1'],
            'discount_amount' => ['required', 'numeric', 'min:0.01'],
            'is_active' => ['nullable', 'boolean'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        return [
            ...$data,
            'is_active' => $request->boolean('is_active', $defaultActive),
        ];
    }
}
