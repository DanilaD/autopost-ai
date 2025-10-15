<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\InstagramAccount;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Test Instagram Account Ownership and Access Control
 *
 * Tests the hybrid ownership model where accounts can be owned by
 * users or companies, and can be shared between users.
 */
class InstagramAccountOwnershipTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function user_can_own_instagram_accounts()
    {
        $user = User::factory()->create();

        $account = InstagramAccount::factory()
            ->forUser($user)
            ->create();

        $this->assertTrue($account->isUserOwned());
        $this->assertFalse($account->isCompanyOwned());
        $this->assertTrue($account->isOwnedBy($user));
        $this->assertEquals('user', $account->ownership_type);
        $this->assertEquals($user->id, $account->user_id);
        $this->assertNull($account->company_id);
    }

    #[Test]
    public function company_can_own_instagram_accounts()
    {
        $company = Company::factory()->create();

        $account = InstagramAccount::factory()
            ->forCompany($company)
            ->create();

        $this->assertTrue($account->isCompanyOwned());
        $this->assertFalse($account->isUserOwned());
        $this->assertEquals('company', $account->ownership_type);
        $this->assertEquals($company->id, $account->company_id);
        $this->assertNull($account->user_id);
    }

    #[Test]
    public function user_has_access_to_their_owned_accounts()
    {
        $user = User::factory()->create();
        $account = InstagramAccount::factory()->forUser($user)->create();

        $this->assertTrue($account->isAccessibleBy($user));
        $this->assertTrue($account->canUserPost($user));
        $this->assertTrue($account->canUserManage($user));
    }

    #[Test]
    public function company_members_have_access_to_company_accounts()
    {
        $company = Company::factory()->create();
        $user = User::factory()->create();

        // Add user to company
        $company->users()->attach($user, ['role' => 'member']);

        $account = InstagramAccount::factory()->forCompany($company)->create();

        $this->assertTrue($account->isAccessibleBy($user));
        $this->assertTrue($account->canUserPost($user));
    }

    #[Test]
    public function company_admins_can_manage_company_accounts()
    {
        $company = Company::factory()->create();
        $admin = User::factory()->create();
        $member = User::factory()->create();

        $company->users()->attach($admin, ['role' => 'admin']);
        $company->users()->attach($member, ['role' => 'member']);

        $account = InstagramAccount::factory()->forCompany($company)->create();

        $this->assertTrue($account->canUserManage($admin));
        $this->assertFalse($account->canUserManage($member));
    }

    #[Test]
    public function users_without_access_cannot_access_accounts()
    {
        $owner = User::factory()->create();
        $stranger = User::factory()->create();

        $account = InstagramAccount::factory()->forUser($owner)->create();

        $this->assertFalse($account->isAccessibleBy($stranger));
        $this->assertFalse($account->canUserPost($stranger));
        $this->assertFalse($account->canUserManage($stranger));
    }

    #[Test]
    public function user_can_share_their_account_with_others()
    {
        $owner = User::factory()->create();
        $collaborator = User::factory()->create();

        $account = InstagramAccount::factory()->forUser($owner)->create();

        $account->shareWith($collaborator, canPost: true, canManage: false, sharedBy: $owner);

        $this->assertTrue($account->is_shared);
        $this->assertTrue($account->isAccessibleBy($collaborator));
        $this->assertTrue($account->canUserPost($collaborator));
        $this->assertFalse($account->canUserManage($collaborator));

        // Verify pivot data
        $sharedUser = $account->sharedWithUsers()->where('users.id', $collaborator->id)->first();
        $this->assertNotNull($sharedUser);
        $this->assertTrue((bool) $sharedUser->pivot->can_post);
        $this->assertFalse((bool) $sharedUser->pivot->can_manage);
        $this->assertEquals($owner->id, $sharedUser->pivot->shared_by_user_id);
    }

    #[Test]
    public function user_can_share_account_with_management_permissions()
    {
        $owner = User::factory()->create();
        $manager = User::factory()->create();

        $account = InstagramAccount::factory()->forUser($owner)->create();

        $account->shareWith($manager, canPost: true, canManage: true, sharedBy: $owner);

        $this->assertTrue($account->canUserPost($manager));
        $this->assertTrue($account->canUserManage($manager));
    }

    #[Test]
    public function user_can_revoke_access_to_shared_account()
    {
        $owner = User::factory()->create();
        $collaborator = User::factory()->create();

        $account = InstagramAccount::factory()->forUser($owner)->create();
        $account->shareWith($collaborator, canPost: true);

        $this->assertTrue($account->isAccessibleBy($collaborator));

        $account->revokeAccessFor($collaborator);

        $this->assertFalse($account->isAccessibleBy($collaborator));
        $this->assertFalse($account->canUserPost($collaborator));
    }

    #[Test]
    public function accessible_by_scope_returns_all_accessible_accounts()
    {
        $user = User::factory()->create();
        $company = Company::factory()->create();
        $company->users()->attach($user, ['role' => 'member']);

        // Create various accounts
        $ownedAccount = InstagramAccount::factory()->forUser($user)->create();
        $companyAccount = InstagramAccount::factory()->forCompany($company)->create();

        $sharedAccount = InstagramAccount::factory()
            ->forUser(User::factory()->create())
            ->create();
        $sharedAccount->shareWith($user);

        // Account user has no access to
        InstagramAccount::factory()->forUser(User::factory()->create())->create();

        $accessibleAccounts = InstagramAccount::accessibleBy($user)->get();

        $this->assertCount(3, $accessibleAccounts);
        $this->assertTrue($accessibleAccounts->contains($ownedAccount));
        $this->assertTrue($accessibleAccounts->contains($companyAccount));
        $this->assertTrue($accessibleAccounts->contains($sharedAccount));
    }

    #[Test]
    public function user_owned_scope_filters_correctly()
    {
        $user = User::factory()->create();
        $company = Company::factory()->create();

        InstagramAccount::factory()->forUser($user)->create();
        InstagramAccount::factory()->forCompany($company)->create();

        $userAccounts = InstagramAccount::userOwned()->get();

        $this->assertCount(1, $userAccounts);
        $this->assertEquals('user', $userAccounts->first()->ownership_type);
    }

    #[Test]
    public function company_owned_scope_filters_correctly()
    {
        $user = User::factory()->create();
        $company = Company::factory()->create();

        InstagramAccount::factory()->forUser($user)->create();
        InstagramAccount::factory()->forCompany($company)->create();

        $companyAccounts = InstagramAccount::companyOwned()->get();

        $this->assertCount(1, $companyAccounts);
        $this->assertEquals('company', $companyAccounts->first()->ownership_type);
    }

    #[Test]
    public function display_name_includes_ownership_context()
    {
        $user = User::factory()->create(['name' => 'John Doe']);
        $company = Company::factory()->create(['name' => 'Acme Corp']);

        $userAccount = InstagramAccount::factory()
            ->forUser($user)
            ->create(['username' => 'johndoe']);

        $companyAccount = InstagramAccount::factory()
            ->forCompany($company)
            ->create(['username' => 'acmecorp']);

        $this->assertEquals('@johndoe (John Doe)', $userAccount->display_name);
        $this->assertEquals('@acmecorp (Acme Corp)', $companyAccount->display_name);
    }

    #[Test]
    public function user_can_have_multiple_instagram_accounts()
    {
        $user = User::factory()->create();

        $account1 = InstagramAccount::factory()->forUser($user)->create();
        $account2 = InstagramAccount::factory()->forUser($user)->create();
        $account3 = InstagramAccount::factory()->forUser($user)->create();

        $this->assertCount(3, $user->ownedInstagramAccounts);
        $this->assertTrue($user->ownedInstagramAccounts->contains($account1));
        $this->assertTrue($user->ownedInstagramAccounts->contains($account2));
        $this->assertTrue($user->ownedInstagramAccounts->contains($account3));
    }

    #[Test]
    public function company_can_have_multiple_instagram_accounts()
    {
        $company = Company::factory()->create();

        InstagramAccount::factory()->count(3)->forCompany($company)->create();

        $this->assertCount(3, $company->instagramAccounts);
    }

    #[Test]
    public function user_can_get_default_instagram_account()
    {
        $user = User::factory()->create();
        $company = Company::factory()->create();
        $company->users()->attach($user, ['role' => 'member']);
        $user->update(['current_company_id' => $company->id]);

        // Create accounts in priority order
        $userAccount = InstagramAccount::factory()->forUser($user)->create();
        $companyAccount = InstagramAccount::factory()->forCompany($company)->create();

        // Should return user's account first
        $default = $user->getDefaultInstagramAccount();
        $this->assertEquals($userAccount->id, $default->id);
    }

    #[Test]
    public function default_account_falls_back_to_company_account()
    {
        $user = User::factory()->create();
        $company = Company::factory()->create();
        $company->users()->attach($user, ['role' => 'member']);
        $user->update(['current_company_id' => $company->id]);

        // Only company account exists
        $companyAccount = InstagramAccount::factory()->forCompany($company)->create();

        $default = $user->getDefaultInstagramAccount();
        $this->assertEquals($companyAccount->id, $default->id);
    }
}
