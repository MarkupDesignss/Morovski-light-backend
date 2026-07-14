<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Item;
use App\Models\User;
use App\Models\Category;
use App\Models\Address;
use Illuminate\Http\Request;
use Mpdf\Mpdf;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Log;
use Symfony\Component\ErrorHandler\Error\FatalError;

class ItemPDFController extends Controller
{
    /**
     * Generate PDF as downloadable file
     */
    public function generatePDF(Request $request)
    {
        try {
            Log::info('PDF Generation Request', [
                'item_ids' => $request->item_ids,
                'all' => $request->all()
            ]);

            $validator = Validator::make($request->all(), [
                'item_ids' => 'required|array|min:1',
                'item_ids.*' => 'integer|exists:items,id',
                'show_price' => 'boolean',
                'show_logo' => 'boolean',
                'show_description' => 'boolean',
                'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
                'logo_url' => 'nullable|string|max:2048',
                'user_id' => 'nullable|integer|exists:users,id',
                'address_id' => 'nullable|integer|exists:addresses,id',
                'company_name' => 'nullable|string|max:255',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Check if too many items (prevent memory issues)
            $itemCount = count($request->item_ids);
            if ($itemCount > 100) {
                return response()->json([
                    'success' => false,
                    'message' => "Too many items selected. Maximum recommended is 100 items. You selected {$itemCount} items. Please reduce the selection.",
                    'max_allowed' => 100,
                    'selected_count' => $itemCount
                ], 422);
            }

            $items = Item::with(['category.parent', 'images'])
                ->whereIn('id', $request->item_ids)
                ->get();

            if ($items->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No items found.'
                ], 404);
            }

            $data = $this->prepareData($items, $request);

            $html = View::make('pdf.items', $data)->render();

            $mpdf = new Mpdf([
                'format' => 'A4',
                'orientation' => 'P',
                'margin_left' => 0,
                'margin_right' => 0,
                'margin_top' => 0,
                'margin_bottom' => 0,
                'default_font' => 'dejavusans',
                'autoScriptToLang' => true,
                'autoLangToFont' => true,
                'allow_charset_conversion' => true,
                'charset_in' => 'utf-8',
                'showImageErrors' => false,
                'curlAllowUnsafeSslRequests' => true,
                'tempDir' => storage_path('app/temp'),
                'debug' => false,
            ]);

            // Add custom CSS for better rendering
            $mpdf->WriteHTML($html);

            return response(
                $mpdf->Output('catalogue.pdf', 'S'),
                200,
                [
                    'Content-Type' => 'application/pdf',
                    'Content-Disposition' => 'attachment; filename="catalogue.pdf"',
                ]
            );

        } catch (\Exception $e) {
            Log::error('PDF Generation Error: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            
            // Check if it's a memory error
            if ($this->isMemoryError($e)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Memory limit exceeded. The PDF is too large to generate. Please try with fewer items.',
                    'error_type' => 'memory_error',
                    'suggestion' => 'Please select fewer items (max 100-150) or generate PDF in batches.'
                ], 500);
            }
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate PDF',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generate PDF and return as base64
     */
    public function generatePDFBase64(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'item_ids' => 'required|array|min:1',
                'item_ids.*' => 'integer|exists:items,id',
                'show_price' => 'boolean',
                'show_logo' => 'boolean',
                'show_description' => 'boolean',
                'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
                'logo_url' => 'nullable|string|max:2048',
                'user_id' => 'nullable|integer|exists:users,id',
                'address_id' => 'nullable|integer|exists:addresses,id',
                'company_name' => 'nullable|string|max:255',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Check if too many items
            $itemCount = count($request->item_ids);
            if ($itemCount > 150) {
                return response()->json([
                    'success' => false,
                    'message' => "Too many items selected. Maximum recommended is 150 items. You selected {$itemCount} items.",
                    'max_allowed' => 150,
                    'selected_count' => $itemCount
                ], 422);
            }

            $items = Item::with(['category.parent', 'images'])
                ->whereIn('id', $request->item_ids)
                ->get();

            if ($items->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No items found.'
                ], 404);
            }

            $data = $this->prepareData($items, $request);

            $html = View::make('pdf.items', $data)->render();

            $mpdf = new Mpdf([
                'format' => 'A4',
                'orientation' => 'P',
                'margin_left' => 0,
                'margin_right' => 0,
                'margin_top' => 0,
                'margin_bottom' => 0,
                'default_font' => 'dejavusans',
                'autoScriptToLang' => true,
                'autoLangToFont' => true,
                'allow_charset_conversion' => true,
                'charset_in' => 'utf-8',
                'tempDir' => storage_path('app/temp'),
            ]);

            $mpdf->WriteHTML($html);
            $pdfContent = $mpdf->Output('', 'S');

            return response()->json([
                'success' => true,
                'pdf_base64' => base64_encode($pdfContent),
                'file_name' => 'catalogue.pdf',
                'items_count' => $items->count(),
                'total_amount' => $items->sum('price'),
            ]);

        } catch (\Exception $e) {
            Log::error('PDF Base64 Error: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            
            if ($this->isMemoryError($e)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Memory limit exceeded. The PDF is too large to generate. Please try with fewer items.',
                    'error_type' => 'memory_error',
                    'suggestion' => 'Please select fewer items (max 100-150) or generate PDF in batches.'
                ], 500);
            }
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate PDF',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Preview PDF in browser
     */
    public function previewPDF(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'item_ids' => 'required|array|min:1',
                'item_ids.*' => 'integer|exists:items,id',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Check if too many items for preview
            $itemCount = count($request->item_ids);
            if ($itemCount > 150) {
                return response()->json([
                    'success' => false,
                    'message' => "Too many items for preview. Maximum recommended is 150 items. You selected {$itemCount} items.",
                    'max_allowed' => 150,
                    'selected_count' => $itemCount
                ], 422);
            }

            $items = Item::with(['category.parent', 'images'])
                ->whereIn('id', $request->item_ids)
                ->get();

            if ($items->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No items found.'
                ], 404);
            }

            $data = $this->prepareData($items, $request);
            return view('pdf.items', $data);

        } catch (\Exception $e) {
            Log::error('Preview Error: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            
            if ($this->isMemoryError($e)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Memory limit exceeded. The preview is too large to display. Please try with fewer items.',
                    'error_type' => 'memory_error',
                    'suggestion' => 'Please select fewer items (max 100-150) for preview.'
                ], 500);
            }
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to preview PDF',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Check if the error is related to memory exhaustion
     */
    private function isMemoryError($exception): bool
    {
        $message = $exception->getMessage();
        $memoryPatterns = [
            'Allowed memory size',
            'memory exhausted',
            'memory limit',
            'out of memory',
            'MemoryError'
        ];

        foreach ($memoryPatterns as $pattern) {
            if (stripos($message, $pattern) !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * Prepare data for the view
     */
    // private function prepareData($items, Request $request): array
    // {
    //     try {
    //         $logoPath = $this->getLogoPath($request);
    //         $logoExists = $this->checkLogoExists($logoPath);
            
    //         $user = null;
    //         $address = null;
            
    //         if ($request->filled('user_id')) {
    //             $user = \App\Models\User::with('addresses')->find($request->user_id);
    //         } elseif (auth()->check()) {
    //             $user = auth()->user()->load('addresses');
    //         }
            
    //         if ($request->filled('address_id')) {
    //             $address = Address::find($request->address_id);
    //         } elseif ($user) {
    //             $address = $user->addresses()->where('is_default', true)->first();
    //             if (!$address && $user->addresses->isNotEmpty()) {
    //                 $address = $user->addresses->first();
    //             }
    //         }

    //         $categories = Category::with('items')->get();

    //         $company = (object) [
    //             'name' => $request->input('company_name', 'Catalogue'),
    //             'tagline' => 'Illuminate Spaces, Elevate Style',
    //             'foreword_title' => 'Light, Crafted Like an Heirloom',
    //             'foreword_text' => 'Every fixture in this catalogue is designed the way a jeweller sets a stone — with proportion, patience, and an eye for how light will fall across a room for decades to come. Brass is hand-turned, crystal is cut in small batches, and every finish is aged to a warmth that only improves with time.',
    //             'foreword_text_2' => 'This collection is organised into six worlds: Chandeliers for the room that commands attention, Pendants for the table that gathers a family, Wall Lights for the hallway that welcomes a guest, Table and Floor Lamps for quiet corners, and Outdoor Lights for the threshold that greets them all.',
    //             'signature' => 'The Design Atelier',
    //             'established' => 'EST. 1998',
    //             'established_text' => 'Handcrafted Since 1998',
    //             'address' => '42 Heritage Court, Greater Noida, Uttar Pradesh, India',
    //             'email' => 'support@morovski.com',
    //             'phone' => '+91 98XXX XXXXX',
    //             'website' => 'www.morovskilight.in',
    //             'social' => '@Morvoski.lights',
    //             'categories' => 'Chandeliers · Pendants · Wall · Table · Floor · Outdoor',
    //         ];

    //         return [
    //             'items' => $items,
    //             'totalItems' => $items->count(),
    //             'totalAmount' => $items->sum('price'),
    //             'showPrice' => $request->boolean('show_price', true),
    //             'showLogo' => $request->boolean('show_logo', true),
    //             'showDescription' => $request->boolean('show_description', false),
    //             'logoPath' => $logoPath,
    //             'logoExists' => $logoExists,
    //             'generatedDate' => now()->format('d M Y H:i'),
    //             'user' => $user,
    //             'address' => $address,
    //             'company' => $company,
    //             'categories' => $categories,
    //             'company_name' => $request->input('company_name', 'Catalogue'),
    //         ];

    //     } catch (\Exception $e) {
    //         Log::error('Prepare Data Error: ' . $e->getMessage());
    //         Log::error('Stack trace: ' . $e->getTraceAsString());
            
    //         // Return minimal data to prevent complete failure
    //         return [
    //             'items' => $items,
    //             'totalItems' => $items->count(),
    //             'totalAmount' => $items->sum('price'),
    //             'showPrice' => true,
    //             'showLogo' => true,
    //             'showDescription' => false,
    //             'logoPath' => '',
    //             'logoExists' => false,
    //             'generatedDate' => now()->format('d M Y H:i'),
    //             'user' => null,
    //             'address' => null,
    //             'company' => (object) ['name' => 'Catalogue'],
    //             'categories' => collect(),
    //             'company_name' => 'Catalogue',
    //         ];
    //     }
    // }
    
    /**
 * Prepare data for the view
 */
  /**
 * Prepare data for the view
 */
    private function prepareData($items, Request $request): array
    {
        try {
            $logoPath = $this->getLogoPath($request);
            $logoExists = $this->checkLogoExists($logoPath);
            
            // Get user data from users table
            $user = null;
            $address = null;
            $userFullName = '';
            $userPhone = '';
            $userEmail = '';
            $isB2B = false;
            
            if ($request->filled('user_id')) {
                // Fetch user from users table with their shipping addresses
                $user = \App\Models\User::with(['addresses' => function($query) {
                    $query->where('is_default', true);
                }])->find($request->user_id);
            } elseif (auth()->check()) {
                // Get authenticated user with their default shipping address
                $user = \App\Models\User::with(['addresses' => function($query) {
                    $query->where('is_default', true);
                }])->find(auth()->id());
            }
            
            // Extract user data from users table
            if ($user) {
                $userFullName = $user->full_name ?? $user->name ?? '';
                $userPhone = $user->phone ?? '';
                $userEmail = $user->email ?? '';
                $isB2B = ($user->account_type ?? '') === 'b2b';
                
                // Get address from shipping_addresses table
                if ($request->filled('address_id')) {
                    // Fetch specific address from shipping_addresses table
                    $address = \App\Models\Address::find($request->address_id);
                } elseif ($user->addresses && $user->addresses->isNotEmpty()) {
                    // Use the default address that was eager loaded
                    $address = $user->addresses->first();
                    
                    // If no default address found, get any address
                    if (!$address) {
                        $address = \App\Models\Address::where('user_id', $user->id)
                            ->first();
                    }
                } else {
                    // Try to get any address from shipping_addresses table
                    $address = \App\Models\Address::where('user_id', $user->id)
                        ->first();
                }
            }
            
            // If still no user, check if we have a logged-in user through auth
            if (!$user && auth()->check()) {
                $user = auth()->user();
                $userFullName = $user->full_name ?? $user->name ?? '';
                $userPhone = $user->phone ?? '';
                $userEmail = $user->email ?? '';
                $isB2B = ($user->account_type ?? '') === 'b2b';
                
                // Get address from shipping_addresses table for this user
                $address = \App\Models\Address::where('user_id', $user->id)
                    ->where('is_default', true)
                    ->first();
                    
                if (!$address) {
                    $address = \App\Models\Address::where('user_id', $user->id)
                        ->first();
                }
            }
            
            // Format address from shipping_addresses table fields
            $formattedAddress = '';
            if ($address) {
                $addressParts = [];
                if ($address->address_line_1) $addressParts[] = $address->address_line_1;
                if ($address->address_line_2) $addressParts[] = $address->address_line_2;
                if ($address->city) $addressParts[] = $address->city;
                if ($address->state) $addressParts[] = $address->state;
                if ($address->postal_code) $addressParts[] = $address->postal_code;
                if ($address->country) $addressParts[] = $address->country;
                $formattedAddress = implode(', ', $addressParts);
            }
            
            $categories = Category::with('items')->get();
    
            // Build company data - using fields from users and shipping_addresses tables
            $companyData = [
                'name' => $request->input('company_name', 'Catalogue'),
                'tagline' => 'Illuminate Spaces, Elevate Style',
                'foreword_title' => 'Light, Crafted Like an Heirloom',
                'foreword_text' => 'Every fixture in this catalogue is designed the way a jeweller sets a stone — with proportion, patience, and an eye for how light will fall across a room for decades to come. Brass is hand-turned, crystal is cut in small batches, and every finish is aged to a warmth that only improves with time.',
                'foreword_text_2' => 'This collection is organised into six worlds: Chandeliers for the room that commands attention, Pendants for the table that gathers a family, Wall Lights for the hallway that welcomes a guest, Table and Floor Lamps for quiet corners, and Outdoor Lights for the threshold that greets them all.',
                'signature' => 'The Design Atelier',
                'established' => 'EST. 1998',
                'established_text' => 'Handcrafted Since 1998',
                // Use user data from users table or fallback to defaults
                'address' => $formattedAddress ?: '42 Heritage Court, Greater Noida, Uttar Pradesh, India',
                'email' => $userEmail ?: 'support@morovski.com',
                'phone' => $userPhone ?: '+91 98XXX XXXXX',
                'website' => 'www.morovskilight.in',
                'social' => '@Morvoski.lights',
                'categories' => 'Chandeliers · Pendants · Wall · Table · Floor · Outdoor',
                'user_full_name' => $userFullName,
                'user_phone' => $userPhone,
                'user_email' => $userEmail,
                'account_type' => $user ? $user->account_type : 'retail',
                // Address fields from shipping_addresses table
                'address_line_1' => $address ? $address->address_line_1 : '',
                'address_line_2' => $address ? $address->address_line_2 : '',
                'city' => $address ? $address->city : '',
                'state' => $address ? $address->state : '',
                'postal_code' => $address ? $address->postal_code : '',
                'country' => $address ? $address->country : '',
            ];
    
            $company = (object) $companyData;
    
            return [
                'items' => $items,
                'totalItems' => $items->count(),
                'totalAmount' => $items->sum('price'),
                'showPrice' => $request->boolean('show_price', true),
                'showLogo' => $request->boolean('show_logo', true),
                'showDescription' => $request->boolean('show_description', false),
                'logoPath' => $logoPath,
                'logoExists' => $logoExists,
                'generatedDate' => now()->format('d M Y H:i'),
                'user' => $user,
                'address' => $address,
                'company' => $company,
                'categories' => $categories,
                'company_name' => $request->input('company_name', 'Catalogue'),
                'isB2B' => $isB2B,
                'userPhone' => $userPhone,
                'userEmail' => $userEmail,
                'userFullName' => $userFullName,
                'companyData' => $companyData,
                'companyInitial' => $userFullName ? strtoupper(substr($userFullName, 0, 1)) : 'M',
                // Add individual address fields for easier access in blade
                'address_line_1' => $address ? $address->address_line_1 : '',
                'address_line_2' => $address ? $address->address_line_2 : '',
                'city' => $address ? $address->city : '',
                'state' => $address ? $address->state : '',
                'postal_code' => $address ? $address->postal_code : '',
                'country' => $address ? $address->country : '',
            ];
    
        } catch (\Exception $e) {
            Log::error('Prepare Data Error: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            
            // Return minimal data with fallback
            return [
                'items' => $items,
                'totalItems' => $items->count(),
                'totalAmount' => $items->sum('price'),
                'showPrice' => true,
                'showLogo' => true,
                'showDescription' => false,
                'logoPath' => '',
                'logoExists' => false,
                'generatedDate' => now()->format('d M Y H:i'),
                'user' => null,
                'address' => null,
                'company' => (object) [
                    'name' => 'Catalogue', 
                    'phone' => '+91 98XXX XXXXX', 
                    'email' => 'support@morovski.com', 
                    'address' => '42 Heritage Court, Greater Noida, Uttar Pradesh, India',
                    'tagline' => 'Illuminate Spaces, Elevate Style',
                    'website' => 'www.morovskilight.in',
                    'social' => '@Morvoski.lights',
                ],
                'categories' => collect(),
                'company_name' => 'Catalogue',
                'isB2B' => false,
                'userPhone' => '',
                'userEmail' => '',
                'userFullName' => '',
                'companyData' => [
                    'name' => 'Catalogue',
                    'phone' => '+91 98XXX XXXXX',
                    'email' => 'support@morovski.com',
                    'address' => '42 Heritage Court, Greater Noida, Uttar Pradesh, India',
                    'user_full_name' => '',
                    'tagline' => 'Illuminate Spaces, Elevate Style',
                    'website' => 'www.morovskilight.in',
                    'social' => '@Morvoski.lights',
                ],
                'companyInitial' => 'M',
                'address_line_1' => '',
                'address_line_2' => '',
                'city' => '',
                'state' => '',
                'postal_code' => '',
                'country' => '',
            ];
        }
    }
    
    /**
     * Format address for display
     */
    private function formatAddress($address): string
    {
        if (!$address) {
            return '';
        }
        
        $parts = [];
        
        if ($address->address_line_1) {
            $parts[] = $address->address_line_1;
        }
        
        if ($address->address_line_2) {
            $parts[] = $address->address_line_2;
        }
        
        if ($address->city) {
            $parts[] = $address->city;
        }
        
        if ($address->state) {
            $parts[] = $address->state;
        }
        
        if ($address->postal_code) {
            $parts[] = $address->postal_code;
        }
        
        if ($address->country) {
            $parts[] = $address->country;
        }
        
        return implode(', ', $parts);
    }

    /**
     * Check if logo exists
     */
    private function checkLogoExists($logoPath): bool
    {
        try {
            if (!$logoPath) return false;
            if (strpos($logoPath, 'data:') === 0) return true;
            if (filter_var($logoPath, FILTER_VALIDATE_URL)) return true;
            if (file_exists(public_path($logoPath))) return true;
            if (file_exists(storage_path('app/public/' . str_replace('storage/', '', $logoPath)))) return true;
            return false;

        } catch (\Exception $e) {
            Log::error('Check Logo Error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get logo path
     */
    private function getLogoPath(Request $request): string
    {
        try {
            if ($request->hasFile('logo')) {
                try {
                    $file = $request->file('logo');
                    $filename = 'logo_' . time() . '_' . Str::random(10) . '.' . $file->getClientOriginalExtension();
                    $path = $file->storeAs('logos', $filename, 'public');
                    
                    $fullPublicPath = public_path('storage/logos/' . $filename);
                    if (file_exists($fullPublicPath)) {
                        $imageContent = file_get_contents($fullPublicPath);
                        $mimeType = mime_content_type($fullPublicPath) ?: 'image/png';
                        return 'data:' . $mimeType . ';base64,' . base64_encode($imageContent);
                    }
                    return Storage::url($path);
                } catch (\Exception $e) {
                    Log::error('Logo Upload Error: ' . $e->getMessage());
                    return '';
                }
            }

            if ($request->filled('logo_url')) {
                $logoUrl = $request->input('logo_url');
                if (filter_var($logoUrl, FILTER_VALIDATE_URL)) {
                    try {
                        $imageContent = @file_get_contents($logoUrl);
                        if ($imageContent !== false) {
                            $finfo = finfo_open(FILEINFO_MIME_TYPE);
                            $mimeType = finfo_buffer($finfo, $imageContent);
                            finfo_close($finfo);
                            return 'data:' . $mimeType . ';base64,' . base64_encode($imageContent);
                        }
                        return $logoUrl;
                    } catch (\Exception $e) {
                        Log::error('Logo URL Fetch Error: ' . $e->getMessage());
                        return $logoUrl;
                    }
                }
                return $logoUrl;
            }

            return '';

        } catch (\Exception $e) {
            Log::error('Get Logo Path Error: ' . $e->getMessage());
            return '';
        }
    }
}