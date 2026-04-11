# Backend Integration Summary - Lumina Academy
## For: Chihab & Salah (Backend Developers)

---

## Overview
The visitor page has been enhanced with new features that will require backend API endpoints and database models. This document outlines the backend requirements for full integration with the current frontend implementation.

---

## Database Models & Entities

### 1. **Language Model**
**Purpose**: Store all available languages offered by Lumina Academy

**Fields**:
```sql
CREATE TABLE languages (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    code VARCHAR(5) UNIQUE NOT NULL,              -- en, es, fr, de, it, pt, jp
    name VARCHAR(100) NOT NULL,                    -- English, Spanish, etc.
    title VARCHAR(150) NOT NULL,                   -- English Mastery, Spanish Immersion, etc.
    description TEXT NOT NULL,                     -- Short description (max 150 chars)
    full_description LONGTEXT NOT NULL,            -- Detailed program description
    flag_url VARCHAR(500) NOT NULL,                -- CDN URL or local asset path
    sort_order INT DEFAULT 0,                      -- For ordering in slider
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

### 2. **Certification Model**
**Purpose**: Store exam certifications for each language

**Fields**:
```sql
CREATE TABLE certifications (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    language_id BIGINT NOT NULL,                   -- Foreign key to languages
    name VARCHAR(100) NOT NULL,                    -- Cambridge ESOL, DELE, DELF, etc.
    description TEXT,
    exams JSON NOT NULL,                           -- Array of exam names
    sort_order INT DEFAULT 0,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    
    FOREIGN KEY (language_id) REFERENCES languages(id) ON DELETE CASCADE,
    KEY (language_id)
);
```

**Example Certification Entry**:
```json
{
    "language_id": 1,
    "name": "Cambridge ESOL",
    "exams": ["KET", "PET", "FCE", "CAE", "CPE"],
    "sort_order": 1
}
```

### 3. **Pricing Tier Model** (NEW)
**Purpose**: Manage course pricing tiers

**Fields**:
```sql
CREATE TABLE pricing_tiers (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    tier_name VARCHAR(100) NOT NULL,               -- Starter, Professional, Elite
    price_monthly DECIMAL(10, 2) NOT NULL,         -- 199, 449, 799
    description TEXT NOT NULL,
    features JSON NOT NULL,                        -- Array of features
    is_popular BOOLEAN DEFAULT FALSE,              -- Mark Professional tier
    sort_order INT DEFAULT 0,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

**Example Pricing Entry**:
```json
{
    "tier_name": "Professional",
    "price_monthly": 449.00,
    "description": "For serious learners",
    "features": [
        "Semi-private classes (3x/week)",
        "1-on-1 coaching (1x/month)",
        "Exam prep modules",
        "Cognitive progress analytics"
    ],
    "is_popular": true
}
```

### 4. **Testimonial Model** (NEW)
**Purpose**: Store student success stories and testimonials

**Fields**:
```sql
CREATE TABLE testimonials (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    student_name VARCHAR(100) NOT NULL,
    certification VARCHAR(100) NOT NULL,           -- Cambridge CAE Certified, etc.
    review TEXT NOT NULL,
    rating INT DEFAULT 5,                          -- 1-5 stars
    language_id BIGINT,                            -- Related language (nullable)
    image_url VARCHAR(500),                        -- Student avatar
    is_featured BOOLEAN DEFAULT FALSE,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    
    FOREIGN KEY (language_id) REFERENCES languages(id) ON DELETE SET NULL,
    KEY (language_id)
);
```

---

## API Endpoints Required

### 1. **Get All Languages** (for Slider)
```
GET /api/languages
```

**Response**:
```json
{
    "success": true,
    "data": [
        {
            "id": 1,
            "code": "en",
            "name": "English",
            "title": "English Mastery",
            "description": "Cambridge preparation and business-level fluency...",
            "full_description": "Master the English language with our comprehensive programs...",
            "flag_url": "https://cdn.example.com/flags/uk.jpg",
            "certifications": [
                {
                    "name": "Cambridge ESOL",
                    "exams": ["KET", "PET", "FCE", "CAE", "CPE"]
                },
                ...
            ]
        },
        ...
    ]
}
```

**Query Parameters**:
- `?active=true` - Only show active languages
- `?limit=10` - Limit results
- `?sort=sort_order` - Sort by field

---

### 2. **Get Language Details** (for Modal)
```
GET /api/languages/{code}
```

**Example**: `GET /api/languages/en`

**Response**:
```json
{
    "success": true,
    "data": {
        "id": 1,
        "code": "en",
        "name": "English",
        "title": "English Mastery",
        "description": "...",
        "full_description": "...",
        "flag_url": "...",
        "certifications": [
            {
                "name": "Cambridge ESOL",
                "exams": ["KET", "PET", "FCE", "CAE", "CPE"]
            },
            {
                "name": "IELTS",
                "exams": ["Academic", "General Training"]
            },
            ...
        ]
    }
}
```

---

### 3. **Get Pricing Tiers**
```
GET /api/pricing-tiers
```

**Response**:
```json
{
    "success": true,
    "data": [
        {
            "id": 1,
            "tier_name": "Starter",
            "price_monthly": 199.00,
            "description": "Perfect for curious beginners",
            "features": [
                "Group classes (2x/week)",
                "Digital learning materials",
                "Student portal access"
            ],
            "is_popular": false
        },
        {
            "id": 2,
            "tier_name": "Professional",
            "price_monthly": 449.00,
            "description": "For serious learners",
            "features": [
                "Semi-private classes (3x/week)",
                "1-on-1 coaching (1x/month)",
                "Exam prep modules",
                "Cognitive progress analytics"
            ],
            "is_popular": true
        },
        ...
    ]
}
```

---

### 4. **Get Testimonials**
```
GET /api/testimonials
```

**Query Parameters**:
- `?featured=true` - Only featured testimonials
- `?language_id={id}` - Filter by language
- `?limit=3` - Limit results

**Response**:
```json
{
    "success": true,
    "data": [
        {
            "id": 1,
            "student_name": "Sarah Mitchell",
            "certification": "Cambridge CAE Certified",
            "review": "I went from beginner to Cambridge Advanced in just 18 months...",
            "rating": 5,
            "language_id": 1,
            "image_url": "https://cdn.example.com/avatars/sarah.jpg",
            "is_featured": true
        },
        ...
    ]
}
```

---

## Admin Panel Endpoints (For Language Management)

### 1. **Create Language**
```
POST /admin/languages
```

**Request Body**:
```json
{
    "code": "en",
    "name": "English",
    "title": "English Mastery",
    "description": "Cambridge preparation...",
    "full_description": "Master the English language...",
    "flag_url": "https://...",
    "sort_order": 0,
    "is_active": true
}
```

---

### 2. **Update Language**
```
PUT /admin/languages/{id}
```

---

### 3. **Delete Language**
```
DELETE /admin/languages/{id}
```

---

### 4. **Attach Certifications to Language**
```
POST /admin/languages/{id}/certifications
```

**Request Body**:
```json
{
    "certification_id": 1
}
```

Or **create new**:
```json
{
    "name": "Cambridge ESOL",
    "exams": ["KET", "PET", "FCE", "CAE", "CPE"],
    "sort_order": 1
}
```

---

## Laravel Models Structure

### **Language Model**
```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Language extends Model
{
    protected $fillable = [
        'code', 'name', 'title', 'description', 
        'full_description', 'flag_url', 'sort_order', 'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer'
    ];

    public function certifications(): HasMany
    {
        return $this->hasMany(Certification::class);
    }

    public function testimonials(): HasMany
    {
        return $this->hasMany(Testimonial::class);
    }

    // Scope for active languages
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // Ordered scope
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('created_at');
    }
}
```

### **Certification Model**
```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Certification extends Model
{
    protected $fillable = ['language_id', 'name', 'description', 'exams', 'sort_order', 'is_active'];

    protected $casts = [
        'exams' => 'array',
        'is_active' => 'boolean'
    ];

    public function language(): BelongsTo
    {
        return $this->belongsTo(Language::class);
    }
}
```

### **PricingTier Model**
```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PricingTier extends Model
{
    protected $table = 'pricing_tiers';
    
    protected $fillable = [
        'tier_name', 'price_monthly', 'description', 
        'features', 'is_popular', 'sort_order', 'is_active'
    ];

    protected $casts = [
        'features' => 'array',
        'is_popular' => 'boolean',
        'is_active' => 'boolean',
        'price_monthly' => 'decimal:2'
    ];
}
```

### **Testimonial Model**
```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Testimonial extends Model
{
    protected $fillable = [
        'student_name', 'certification', 'review', 'rating',
        'language_id', 'image_url', 'is_featured', 'is_active'
    ];

    protected $casts = [
        'rating' => 'integer',
        'is_featured' => 'boolean',
        'is_active' => 'boolean'
    ];

    public function language(): BelongsTo
    {
        return $this->belongsTo(Language::class);
    }
}
```

---

## Controllers Required

### **LanguageController (Public API)**
```php
<?php

namespace App\Http\Controllers\Api;

use App\Models\Language;

class LanguageController extends Controller
{
    public function index()
    {
        $languages = Language::active()
            ->ordered()
            ->with('certifications')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $languages
        ]);
    }

    public function show($code)
    {
        $language = Language::where('code', $code)
            ->active()
            ->with(['certifications', 'testimonials'])
            ->firstOrFail();

        return response()->json([
            'success' => true,
            'data' => $language
        ]);
    }
}
```

### **Admin LanguageController (Admin Panel)**
```php
<?php

namespace App\Http\Controllers\Admin;

use App\Models\Language;

class LanguageController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|unique:languages|max:5',
            'name' => 'required|max:100',
            'title' => 'required|max:150',
            'description' => 'required',
            'full_description' => 'required',
            'flag_url' => 'required|url',
            'sort_order' => 'integer'
        ]);

        $language = Language::create($validated);

        return response()->json([
            'success' => true,
            'data' => $language
        ], 201);
    }

    public function update(Request $request, Language $language)
    {
        $validated = $request->validate([
            'code' => 'required|unique:languages,code,' . $language->id . '|max:5',
            'name' => 'required|max:100',
            'title' => 'required|max:150',
            'description' => 'required',
            'full_description' => 'required',
            'flag_url' => 'required|url',
            'sort_order' => 'integer',
            'is_active' => 'boolean'
        ]);

        $language->update($validated);

        return response()->json([
            'success' => true,
            'data' => $language
        ]);
    }

    public function destroy(Language $language)
    {
        $language->delete();

        return response()->json([
            'success' => true,
            'message' => 'Language deleted successfully'
        ]);
    }
}
```

---

## Routes Configuration

```php
// routes/api.php (Public API)
Route::middleware('api')->group(function () {
    Route::get('/languages', [LanguageController::class, 'index']);
    Route::get('/languages/{code}', [LanguageController::class, 'show']);
    Route::get('/pricing-tiers', [PricingTierController::class, 'index']);
    Route::get('/testimonials', [TestimonialController::class, 'index']);
});

// routes/admin.php (Admin Panel)
Route::middleware(['auth:sanctum', 'admin'])->prefix('admin')->group(function () {
    Route::apiResource('languages', LanguageController::class);
    Route::post('languages/{language}/certifications', [LanguageController::class, 'attachCertification']);
    Route::apiResource('pricing-tiers', PricingTierController::class);
    Route::apiResource('testimonials', TestimonialController::class);
});
```

---

## Migration Files

### **Create Languages Table**
```php
Schema::create('languages', function (Blueprint $table) {
    $table->id();
    $table->string('code', 5)->unique();
    $table->string('name', 100);
    $table->string('title', 150);
    $table->text('description');
    $table->longText('full_description');
    $table->string('flag_url', 500);
    $table->integer('sort_order')->default(0);
    $table->boolean('is_active')->default(true);
    $table->timestamps();
    $table->index('code');
    $table->index('is_active');
});
```

### **Create Certifications Table**
```php
Schema::create('certifications', function (Blueprint $table) {
    $table->id();
    $table->foreignId('language_id')->constrained()->onDelete('cascade');
    $table->string('name', 100);
    $table->text('description')->nullable();
    $table->json('exams');
    $table->integer('sort_order')->default(0);
    $table->boolean('is_active')->default(true);
    $table->timestamps();
    $table->index(['language_id', 'is_active']);
});
```

### **Create Pricing Tiers Table**
```php
Schema::create('pricing_tiers', function (Blueprint $table) {
    $table->id();
    $table->string('tier_name', 100);
    $table->decimal('price_monthly', 10, 2);
    $table->text('description');
    $table->json('features');
    $table->boolean('is_popular')->default(false);
    $table->integer('sort_order')->default(0);
    $table->boolean('is_active')->default(true);
    $table->timestamps();
    $table->index('is_active');
});
```

### **Create Testimonials Table**
```php
Schema::create('testimonials', function (Blueprint $table) {
    $table->id();
    $table->string('student_name', 100);
    $table->string('certification', 100);
    $table->text('review');
    $table->integer('rating')->default(5);
    $table->foreignId('language_id')->nullable()->constrained()->onDelete('set null');
    $table->string('image_url', 500)->nullable();
    $table->boolean('is_featured')->default(false);
    $table->boolean('is_active')->default(true);
    $table->timestamps();
    $table->index(['language_id', 'is_active']);
});
```

---

## Data Seeding

Create a seeder to populate initial data:

```php
<?php

namespace Database\Seeders;

use App\Models\Language;
use App\Models\Certification;
use App\Models\PricingTier;
use Illuminate\Database\Seeder;

class LanguageSeeder extends Seeder
{
    public function run()
    {
        // Create languages with certifications
        $languages = [
            [
                'code' => 'en',
                'name' => 'English',
                'title' => 'English Mastery',
                'description' => 'Cambridge preparation and business-level fluency...',
                'full_description' => 'Master the English language...',
                'flag_url' => 'https://lh3.googleusercontent.com/...',
                'certifications' => [
                    ['name' => 'Cambridge ESOL', 'exams' => ['KET', 'PET', 'FCE', 'CAE', 'CPE']],
                    ['name' => 'IELTS', 'exams' => ['Academic', 'General Training']],
                    ['name' => 'TOEFL', 'exams' => ['iBT']],
                ]
            ],
            // ... more languages
        ];

        foreach ($languages as $langData) {
            $certifications = $langData['certifications'];
            unset($langData['certifications']);

            $language = Language::create($langData);

            foreach ($certifications as $cert) {
                Certification::create([
                    'language_id' => $language->id,
                    'name' => $cert['name'],
                    'exams' => $cert['exams']
                ]);
            }
        }

        // Create pricing tiers
        PricingTier::create([
            'tier_name' => 'Starter',
            'price_monthly' => 199.00,
            'description' => 'Perfect for curious beginners',
            'features' => ['Group classes (2x/week)', 'Digital learning materials', 'Student portal access']
        ]);

        PricingTier::create([
            'tier_name' => 'Professional',
            'price_monthly' => 449.00,
            'description' => 'For serious learners',
            'features' => ['Semi-private classes (3x/week)', '1-on-1 coaching', 'Exam prep modules'],
            'is_popular' => true
        ]);

        PricingTier::create([
            'tier_name' => 'Elite',
            'price_monthly' => 799.00,
            'description' => 'For certification seekers',
            'features' => ['Unlimited 1-on-1 sessions', 'Native speaker sessions', 'Custom curriculum']
        ]);
    }
}
```

---

## Security Considerations

1. **API Rate Limiting**: Implement on public endpoints
   ```php
   Route::middleware('throttle:60,1')->group(function () {
       Route::get('/languages', ...);
   });
   ```

2. **Authentication**: Protect admin endpoints with sanctum
   ```php
   Route::middleware(['auth:sanctum', 'admin'])->group(...);
   ```

3. **Validation**: Always validate input on admin endpoints

4. **Authorization**: Use policies for admin actions
   ```php
   $this->authorize('update', $language);
   ```

5. **Image Storage**: Store flag images securely
   - Use S3 or local storage
   - Implement image validation

---

## Testing Requirements

```php
// Tests/Feature/LanguageApiTest.php
public function test_get_all_languages()
{
    $response = $this->getJson('/api/languages');
    $response->assertStatus(200)
             ->assertJsonStructure(['success', 'data']);
}

public function test_get_language_by_code()
{
    $language = Language::factory()->create(['code' => 'en']);
    $response = $this->getJson('/api/languages/en');
    $response->assertStatus(200);
}

public function test_admin_can_create_language()
{
    $response = $this->actingAs($admin, 'sanctum')
        ->postJson('/admin/languages', [
            'code' => 'de',
            'name' => 'German',
            // ...
        ]);
    $response->assertStatus(201);
}
```

---

## Frontend Integration Points

The frontend currently uses hard-coded data. Update the JavaScript to fetch from APIs:

```javascript
// Replace hard-coded languageData with API call
fetch('/api/languages')
    .then(response => response.json())
    .then(data => {
        // Populate slider with data
        initializeSlider(data.data);
    });

// For modal details
fetch('/api/languages/' + langCode)
    .then(response => response.json())
    .then(data => {
        openLanguageModal(data.data);
    });

// For pricing
fetch('/api/pricing-tiers')
    .then(response => response.json())
    .then(data => {
        populatePricingSection(data.data);
    });
```

---

## Priority Implementation Order

1. **Phase 1**: Language + Certification models and API
2. **Phase 2**: Admin panel for language management
3. **Phase 3**: Pricing tiers API
4. **Phase 4**: Testimonials API
5. **Phase 5**: Frontend integration to dynamic data

---

**Target Completion**: Week of April 14-18, 2026  
**Integration Branch**: feature/backend-api-integration

---

**Questions?** Refer to the Frontend Summary (FRONTEND_SUMMARY_DJIAD.md) for UI integration details.
