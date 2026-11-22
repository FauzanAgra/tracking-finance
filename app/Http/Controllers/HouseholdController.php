<?php

namespace App\Http\Controllers;

use App\Models\Wallet;
use App\Services\HouseholdService;
use DomainException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HouseholdController extends Controller
{
    public function __construct(
        private readonly HouseholdService $householdService
    ) {}

    /**
     * Display household hub or invitation onboarding if not in a household.
     */
    public function index(Request $request): View
    {
        $user = $request->user();
        $membership = $user->householdMember()->with('household.owner')->first();

        $summary = null;
        $userWallets = $user->wallets()->get();

        if ($membership) {
            $summary = $this->householdService->getHouseholdSummary($membership->household);
        }

        return view('household.index', [
            'membership' => $membership,
            'summary' => $summary,
            'userWallets' => $userWallets,
        ]);
    }

    /**
     * Create a new household.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
        ]);

        try {
            $this->householdService->createHousehold($request->user(), $validated['name']);

            return redirect()->route('household.index')->with('success', 'Rumah tangga keluarga berhasil dibentuk!');
        } catch (DomainException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Join an existing household using an invite code.
     */
    public function join(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'invite_code' => ['required', 'string', 'max:20'],
        ]);

        try {
            $this->householdService->joinHousehold($request->user(), $validated['invite_code']);

            return redirect()->route('household.index')->with('success', 'Berhasil bergabung ke dalam rumah tangga!');
        } catch (DomainException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Leave the current household.
     */
    public function leave(Request $request): RedirectResponse
    {
        try {
            $this->householdService->leaveHousehold($request->user());

            return redirect()->route('household.index')->with('success', 'Kamu telah keluar dari rumah tangga.');
        } catch (DomainException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Dissolve/delete the household (owner only).
     */
    public function destroy(Request $request): RedirectResponse
    {
        $membership = $request->user()->householdMember;
        if (! $membership) {
            return back()->with('error', 'Kamu tidak berada di rumah tangga mana pun.');
        }

        try {
            $this->householdService->deleteHousehold($membership->household, $request->user());

            return redirect()->route('household.index')->with('success', 'Rumah tangga telah resmi dibubarkan.');
        } catch (DomainException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Regenerate invitation code (owner only).
     */
    public function regenerateInviteCode(Request $request): RedirectResponse
    {
        $membership = $request->user()->householdMember;
        if (! $membership) {
            return back()->with('error', 'Kamu tidak berada di rumah tangga mana pun.');
        }

        try {
            $code = $this->householdService->regenerateInviteCode($membership->household, $request->user());

            return back()->with('success', "Kode undangan baru berhasil dibuat: {$code}");
        } catch (DomainException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Remove a member from the household (owner only).
     */
    public function removeMember(Request $request, int $userId): RedirectResponse
    {
        $membership = $request->user()->householdMember;
        if (! $membership) {
            return back()->with('error', 'Kamu tidak berada di rumah tangga mana pun.');
        }

        try {
            $this->householdService->removeMember($membership->household, $request->user(), $userId);

            return back()->with('success', 'Anggota berhasil dikeluarkan dari rumah tangga.');
        } catch (DomainException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Toggle whether a wallet is shared with the household.
     */
    public function toggleWalletShare(Request $request, Wallet $wallet): RedirectResponse
    {
        if ($wallet->user_id !== $request->user()->id) {
            abort(403);
        }

        $wallet->update(['is_shared' => ! $wallet->is_shared]);

        $status = $wallet->is_shared ? 'dibagikan ke' : 'disembunyikan dari';

        return back()->with('success', "Dompet {$wallet->name} berhasil {$status} keluarga.");
    }
}
