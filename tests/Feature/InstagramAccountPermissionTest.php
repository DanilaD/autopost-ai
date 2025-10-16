<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\InstagramAccount;
use App\Models\User;
use App\Services\InstagramAccountPermissionService;
use App\Services\InstagramAccountService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Test Instagram Account Permission Service
 *
 * Tests the permission checking logic for viewing, posting, managing,
 * and sharing Instagram accounts.
 */
class InstagramAccountPermissionTest extends TestCase
{
    use RefreshDatabase;

    protected InstagramAccountPermissionService $permissionService;

    protected InstagramAccountService $accountService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->permissionService = app(InstagramAccountPermissionService::class);
        $this->accountService = app(InstagramAccountService::class);
    }

    #[Test]
    public function owner_has_all_permissions_on_their_account()
    {
        $user = User::factory()->create();
        $account = InstagramAccount::factory()->forUser($user)->create();

        $this->assertTrue($this->permissionService->canView($user, $account));
        $this->assertTrue($this->permissionService->canPost($user, $account));
        $this->assertTrue($this->permissionService->canManage($user, $account));
        $this->assertTrue($this->permissionService->canShare($user, $account));
        $this->assertTrue($this->permissionService->canDelete($user, $account));
    }

    #[Test]
    public function company_member_can_view_and_post_but_not_manage()
    {
        $company = Company::factory()->create();
        $member = User::factory()->create();
        $company->users()->attach($member, ['role' => 'member']);

        $account = InstagramAccount::factory()->forCompany($company)->create();

        $this->assertTrue($this->permissionService->canView($member, $account));
        $this->assertTrue($this->permissionService->canPost($member, $account));
        $this->assertFalse($this->permissionService->canManage($member, $account));
        $this->assertFalse($this->permissionService->canShare($member, $account));
        $this->assertFalse($this->permissionService->canDelete($member, $account));
    }

    #[Test]
    public function company_admin_can_manage_company_accounts()
    {
        $company = Company::factory()->create();
        $admin = User::factory()->create();
        $company->users()->attach($admin, ['role' => 'admin']);

        $account = InstagramAccount::factory()->forCompany($company)->create();

        $this->assertTrue($this->permissionService->canView($admin, $account));
        $this->assertTrue($this->permissionService->canPost($admin, $account));
        $this->assertTrue($this->permissionService->canManage($admin, $account));
        $this->assertTrue($this->permissionService->canShare($admin, $account));
        $this->assertTrue($this->permissionService->canDelete($admin, $account));
    }

    #[Test]
    public function shared_user_with_post_permission_can_post()
    {
        $owner = User::factory()->create();
        $collaborator = User::factory()->create();

        $account = InstagramAccount::factory()->forUser($owner)->create();
        $this->accountService->shareWith($account, $collaborator, canPost: true, canManage: false);

        $this->assertTrue($this->permissionService->canView($collaborator, $account));
        $this->assertTrue($this->permissionService->canPost($collaborator, $account));
        $this->assertFalse($this->permissionService->canManage($collaborator, $account));
        $this->assertFalse($this->permissionService->canShare($collaborator, $account));
    }

    #[Test]
    public function shared_user_with_manage_permission_can_manage()
    {
        $owner = User::factory()->create();
        $manager = User::factory()->create();

        $account = InstagramAccount::factory()->forUser($owner)->create();
        $this->accountService->shareWith($account, $manager, canPost: true, canManage: true);

        $this->assertTrue($this->permissionService->canView($manager, $account));
        $this->assertTrue($this->permissionService->canPost($manager, $account));
        $this->assertTrue($this->permissionService->canManage($manager, $account));
        $this->assertFalse($this->permissionService->canShare($manager, $account)); // Still can't share
        $this->assertFalse($this->permissionService->canDelete($manager, $account));
    }

    #[Test]
    public function stranger_has_no_permissions()
    {
        $owner = User::factory()->create();
        $stranger = User::factory()->create();

        $account = InstagramAccount::factory()->forUser($owner)->create();

        $this->assertFalse($this->permissionService->canView($stranger, $account));
        $this->assertFalse($this->permissionService->canPost($stranger, $account));
        $this->assertFalse($this->permissionService->canManage($stranger, $account));
        $this->assertFalse($this->permissionService->canShare($stranger, $account));
        $this->assertFalse($this->permissionService->canDelete($stranger, $account));
    }

    #[Test]
    public function get_accessible_accounts_with_permissions_returns_correct_data()
    {
        $user = User::factory()->create();
        $company = Company::factory()->create();
        $company->users()->attach($user, ['role' => 'member']);

        // User owns this account
        $ownedAccount = InstagramAccount::factory()->forUser($user)->create();

        // User is member of company with this account
        $companyAccount = InstagramAccount::factory()->forCompany($company)->create();

        // Account shared with user
        $sharedAccount = InstagramAccount::factory()
            ->forUser(User::factory()->create())
            ->create();
        $this->accountService->shareWith($sharedAccount, $user, canPost: true, canManage: false);

        $result = $this->permissionService->getAccessibleAccountsWithPermissions($user);

        $this->assertCount(3, $result);

        // Check owned account
        $ownedResult = collect($result)->firstWhere('account.id', $ownedAccount->id);
        $this->assertEquals('owner', $ownedResult['access_type']);
        $this->assertTrue($ownedResult['permissions']['can_manage']);

        // Check company account
        $companyResult = collect($result)->firstWhere('account.id', $companyAccount->id);
        $this->assertEquals('company', $companyResult['access_type']);
        $this->assertTrue($companyResult['permissions']['can_post']);
        $this->assertFalse($companyResult['permissions']['can_manage']);

        // Check shared account
        $sharedResult = collect($result)->firstWhere('account.id', $sharedAccount->id);
        $this->assertEquals('shared', $sharedResult['access_type']);
        $this->assertTrue($sharedResult['permissions']['can_post']);
        $this->assertFalse($sharedResult['permissions']['can_manage']);
    }

    #[Test]
    public function owner_can_share_their_account()
    {
        $owner = User::factory()->create();
        $collaborator = User::factory()->create();

        $account = InstagramAccount::factory()->forUser($owner)->create();

        $shared = $this->permissionService->shareAccount(
            $account,
            $collaborator,
            $owner,
            canPost: true,
            canManage: false
        );

        $this->assertTrue($shared);
        $this->assertTrue($account->sharedWithUsers()->where('users.id', $collaborator->id)->exists());
    }

    #[Test]
    public function non_owner_cannot_share_user_account()
    {
        $owner = User::factory()->create();
        $stranger = User::factory()->create();
        $targetUser = User::factory()->create();

        $account = InstagramAccount::factory()->forUser($owner)->create();

        $shared = $this->permissionService->shareAccount(
            $account,
            $targetUser,
            $stranger, // Stranger trying to share
            canPost: true
        );

        $this->assertFalse($shared);
        $this->assertFalse($account->sharedWithUsers()->where('users.id', $targetUser->id)->exists());
    }

    #[Test]
    public function company_admin_can_share_company_account()
    {
        $company = Company::factory()->create();
        $admin = User::factory()->create();
        $targetUser = User::factory()->create();

        $company->users()->attach($admin, ['role' => 'admin']);

        $account = InstagramAccount::factory()->forCompany($company)->create();

        $shared = $this->permissionService->shareAccount(
            $account,
            $targetUser,
            $admin,
            canPost: true
        );

        $this->assertTrue($shared);
    }

    #[Test]
    public function company_member_cannot_share_company_account()
    {
        $company = Company::factory()->create();
        $member = User::factory()->create();
        $targetUser = User::factory()->create();

        $company->users()->attach($member, ['role' => 'member']);

        $account = InstagramAccount::factory()->forCompany($company)->create();

        $shared = $this->permissionService->shareAccount(
            $account,
            $targetUser,
            $member,
            canPost: true
        );

        $this->assertFalse($shared);
    }

    #[Test]
    public function owner_can_revoke_access()
    {
        $owner = User::factory()->create();
        $collaborator = User::factory()->create();

        $account = InstagramAccount::factory()->forUser($owner)->create();
        $this->accountService->shareWith($account, $collaborator);

        $revoked = $this->permissionService->revokeAccess(
            $account,
            $collaborator,
            $owner
        );

        $this->assertTrue($revoked);
        $this->assertFalse($account->sharedWithUsers()->where('users.id', $collaborator->id)->exists());
    }

    #[Test]
    public function cannot_revoke_own_access()
    {
        $owner = User::factory()->create();
        $account = InstagramAccount::factory()->forUser($owner)->create();

        $revoked = $this->permissionService->revokeAccess(
            $account,
            $owner,
            $owner
        );

        $this->assertFalse($revoked);
    }

    #[Test]
    public function stranger_cannot_revoke_access()
    {
        $owner = User::factory()->create();
        $collaborator = User::factory()->create();
        $stranger = User::factory()->create();

        $account = InstagramAccount::factory()->forUser($owner)->create();
        $this->accountService->shareWith($account, $collaborator);

        $revoked = $this->permissionService->revokeAccess(
            $account,
            $collaborator,
            $stranger // Stranger trying to revoke
        );

        $this->assertFalse($revoked);
    }

    #[Test]
    public function authorize_throws_exception_when_unauthorized()
    {
        $owner = User::factory()->create();
        $stranger = User::factory()->create();

        $account = InstagramAccount::factory()->forUser($owner)->create();

        $this->expectException(\Illuminate\Auth\Access\AuthorizationException::class);

        $this->permissionService->authorize($stranger, $account, 'manage');
    }

    #[Test]
    public function authorize_passes_when_authorized()
    {
        $owner = User::factory()->create();
        $account = InstagramAccount::factory()->forUser($owner)->create();

        // Should not throw exception
        $this->permissionService->authorize($owner, $account, 'manage');

        $this->assertTrue(true); // If we get here, authorization passed
    }

    #[Test]
    public function get_access_type_returns_correct_value()
    {
        $owner = User::factory()->create();
        $account = InstagramAccount::factory()->forUser($owner)->create();

        $company = Company::factory()->create();
        $member = User::factory()->create();
        $company->users()->attach($member, ['role' => 'member']);
        $companyAccount = InstagramAccount::factory()->forCompany($company)->create();

        $sharedUser = User::factory()->create();
        $this->accountService->shareWith($account, $sharedUser);

        $stranger = User::factory()->create();

        $this->assertEquals('owner', $this->permissionService->getAccessType($owner, $account));
        $this->assertEquals('company', $this->permissionService->getAccessType($member, $companyAccount));
        $this->assertEquals('shared', $this->permissionService->getAccessType($sharedUser, $account));
        $this->assertEquals('none', $this->permissionService->getAccessType($stranger, $account));
    }
}
