## Integration in your Business Logic
In the actual application code (e.g., inside a Ticket Service or a Webhook Handler), we will call the APIs like this:

```
use App\Services\ApiCallerService;
use App\Models\ApiConfig;
// ... inside a class ...
public function syncData($configId) {
    $config = ApiConfig::find($configId);
    $response = app(ApiCallerService::class)->call($config, [
        'body' => ['user_id' => 123] // runtime data
    ]);
    
    if ($response['status'] === 200) {
        // Process $response['body']
    }
}
```