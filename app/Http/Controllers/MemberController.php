<?php

namespace App\Http\Controllers;

use App\Http\Requests\MemberRequest;
use App\Models\Member;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class MemberController extends Controller
{
    public function index(): View
    {
        $members = Member::query()
            ->withCount('sales')
            ->withSum('sales', 'grand_total')
            ->latest()
            ->get();

        return view('members.index', compact('members'));
    }

    public function store(MemberRequest $request): RedirectResponse
    {
        Member::query()->create([
            ...$request->validated(),
            'points_balance' => (int) $request->input('points_balance', 0),
            'is_active' => $request->boolean('is_active', true),
        ]);

        return back()->with('status', 'Member berhasil ditambahkan.');
    }

    public function update(MemberRequest $request, Member $member): RedirectResponse
    {
        $member->update([
            ...$request->validated(),
            'is_active' => $request->boolean('is_active'),
        ]);

        return back()->with('status', 'Data member berhasil diperbarui.');
    }

    public function destroy(Member $member): RedirectResponse
    {
        if ($member->sales()->exists()) {
            return back()->with('error', 'Member sudah punya histori transaksi. Nonaktifkan saja agar histori tetap rapi.');
        }

        $member->delete();

        return back()->with('status', 'Member berhasil dihapus.');
    }
}
