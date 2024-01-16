<?php

namespace Feature\Controllers\Others;

use App\Models\Customer;
use App\Models\Document;
use App\Models\Location;
use App\Models\Profile;
use App\Models\Security;
use App\Models\User;
use Tests\TestCase;

class DocumentControllerTest extends TestCase
{
    public function testDocumentIndexAction()
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->for($customer)->create();
        $location = Location::factory()->for($customer)->create(['locationName' => 'locationA']);
        $profile = Profile::factory()->for($customer)->for($user)->for($location)->create();
        Security::factory()->for($user)->for($location)->for($profile)->create();

        $headers = $this->authenticateUser($user);
        Document::factory()->for($customer)->for($location)->count(5)->create();
        $response = $this->withHeaders($headers)->get('/api/document?perPage=10&locationID='.$location->locationID.'&scope=global&orderByField=created_at&orderBy=desc');

        $response->assertStatus(200);
        $response->assertJsonCount(5, 'data.data');
    }

    public function testDocumentIndexSearchAction()
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->for($customer)->create();
        $location = Location::factory()->for($customer)->create(['locationName' => 'locationA']);
        $profile = Profile::factory()->for($customer)->for($user)->for($location)->create();
        Security::factory()->for($user)->for($location)->for($profile)->create();

        $headers = $this->authenticateUser($user);
        Document::factory()->for($customer)->for($location)->create(['documentFileName' => 'test']);
        Document::factory()->for($customer)->for($location)->count(5)->create();

        $response = $this->withHeaders($headers)->get('/api/document?search=test');
        $response->assertStatus(200);
        $response->assertJsonCount(1, 'data.data');
    }

    public function testDocumentIndexOrderByAction()
    {
        $customer = Customer::factory()->create();
        $user1 = User::factory()->for($customer)->create();
        $user2 = User::factory()->for($customer)->create();
        $location = Location::factory()->for($customer)->create(['locationName' => 'locationA']);
        $profile = Profile::factory()->for($customer)->for($user1)->for($location)->create();
        Security::factory()->for($user1)->for($location)->for($profile)->create();

        $headers = $this->authenticateUser($user1);
        $document1 = Document::factory()->for($user2, 'uploadedByUser')->for($customer)->for($location)->create(['documentFileName' => 'test 2']);
        $document2 = Document::factory()->for($user1, 'uploadedByUser')->for($customer)->for($location)->create(['documentFileName' => 'test 1']);

        $response = $this->withHeaders($headers)->get('/api/document?orderBy=desc&orderByField=uploadedByUser');
        $response->assertStatus(200);
        $response->assertJsonCount(2, 'data.data');
    }

    public function testDocumentStoreAction()
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->for($customer)->create();
        $location = Location::factory()->for($customer)->create(['locationName' => 'locationA']);
        $profile = Profile::factory()->for($customer)->for($user)->for($location)->create();
        Security::factory()->for($user)->for($location)->for($profile)->create();

        $headers = $this->authenticateUser($user);

        $documentData = [
            'documentFileName' => 'example.pdf',
            'uploadFileName' => 'example.pdf',
            'locationID' => $location->locationID,
        ];

        $response = $this->withHeaders($headers)->post('/api/document', $documentData);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data',
        ]);
    }

    public function testDocumentStoreWithoutLocationAction()
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->for($customer)->create();
        $location = Location::factory()->for($customer)->create(['locationName' => 'locationA']);
        $profile = Profile::factory()->for($customer)->for($user)->for($location)->create();
        Security::factory()->for($user)->for($location)->for($profile)->create();

        $headers = $this->authenticateUser($user);

        $documentData = [
            'documentFileName' => 'example.pdf',
            'uploadFileName' => 'example.pdf',
        ];

        $response = $this->withHeaders($headers)->post('/api/document', $documentData);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data',
        ]);
    }

    public function testDocumentUpdateAction()
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->for($customer)->create();
        $location = Location::factory()->for($customer)->create(['locationName' => 'locationA']);
        $profile = Profile::factory()->for($customer)->for($user)->for($location)->create();
        Security::factory()->for($user)->for($location)->for($profile)->create();

        $headers = $this->authenticateUser($user);

        $document = Document::factory()->for($customer)->for($location)->create();

        $updateData = [
            'documentScope' => 'global',
        ];

        $response = $this->withHeaders($headers)->patch("/api/document/{$document->documentID}", $updateData);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data',
        ]);
    }

    public function testDocumentShowAction()
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->for($customer)->create();
        $location = Location::factory()->for($customer)->create(['locationName' => 'locationA']);
        $profile = Profile::factory()->for($customer)->for($user)->for($location)->create();
        Security::factory()->for($user)->for($location)->for($profile)->create();

        $headers = $this->authenticateUser($user);

        $document = Document::factory()->for($customer)->for($location)->create();

        $response = $this->withHeaders($headers)->get("/api/document/{$document->documentID}");

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data',
        ]);
    }

    public function testDocumentDestroyAction()
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->for($customer)->create();
        $location = Location::factory()->for($customer)->create(['locationName' => 'locationA']);
        $profile = Profile::factory()->for($customer)->for($user)->for($location)->create();
        Security::factory()->for($user)->for($location)->for($profile)->create();

        $headers = $this->authenticateUser($user);

        $document = Document::factory()->for($customer)->for($location)->create();

        $response = $this->withHeaders($headers)->delete("/api/document/{$document->documentID}");
        $response->assertStatus(200);
    }

    public function testDocumentGetPresignedUrlAction()
    {
        $customer = Customer::factory()->create();
        $user = User::factory()->for($customer)->create();
        $location = Location::factory()->for($customer)->create(['locationName' => 'locationA']);
        $profile = Profile::factory()->for($customer)->for($user)->for($location)->create();
        Security::factory()->for($user)->for($location)->for($profile)->create();

        $headers = $this->authenticateUser($user);

        $response = $this->withHeaders($headers)->get('/api/presignedurl?fileName=example.pdf');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data',
        ]);
    }
}
