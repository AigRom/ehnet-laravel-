<?php

namespace App\Services\User;

use App\Models\Conversation;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class DeleteUserAccountService
{
    public function handle(User $user): void
    {
        DB::transaction(function () use ($user) {

            $hasListings = $user->listings()->exists();

            $hasConversations = Conversation::query()
                ->where('seller_id', $user->id)
                ->orWhere('buyer_id', $user->id)
                ->exists();

            if (! $hasListings && ! $hasConversations) {
                $user->delete();

                return;
            }

            $user->listings()
                ->where('status', 'draft')
                ->delete();

            $user->listings()
                ->where('status', '!=', 'draft')
                ->update([
                    'status' => 'deleted',
                ]);

            $user->update([
                'name' => 'Kustutatud kasutaja',
                'email' => 'deleted_'.$user->id.'_'.time().'@example.invalid',
                'email_verified_at' => null,
                'remember_token' => null,
                'avatar_path' => null,
                'phone' => null,

                'first_name' => null,
                'last_name' => null,
                'date_of_birth' => null,

                'contact_first_name' => null,
                'contact_last_name' => null,

                'company_name' => null,
                'company_reg_no' => null,

                'location_id' => null,

                'is_active' => false,
            ]);

            if (isset($user->deleted_at)) {
                $user->deleted_at = now();
                $user->save();
            }
        });
    }
}
