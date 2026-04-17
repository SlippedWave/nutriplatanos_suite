<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Note;



class ProductService
{
    public function createProductNote(Product $product, string $noteContent): void
    {
        Note::create([
            'notable_id' => $product->id,
            'notable_type' => Product::class,
            'content' => $noteContent,
            'user_id' => Auth::id(),
            'type' => 'product'
        ]);
    }


    public function createProduct(array $data): array
    {
        try {
            $validated = $this->validateProductData($data);

            DB::beginTransaction();

            $product = Product::create($validated);

            DB::commit();

            return [
                'success' => true,
                'product' => $product,
                'message' => 'Producto creado exitosamente.'
            ];
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            return [
                'success' => false,
                'message' => 'Error de validación. Hay ' . count($e->errors()) . ' error(es).',
                'type' => 'validation-exception',
                'errors' => $e->errors(),
            ];
        }
        catch (\Exception $e) {
            DB::rollBack();
            return [
                'success' => false,
                'message' => 'Error al crear el producto: ' . $e->getMessage(),
                'type' => 'exception',
            ];
        }
    }

    public function updateProduct(int $id, array $data): array
    {
        try {
            $validated = $this->validateProductData($data);

            
            $product = Product::findOrFail($id);

            DB::beginTransaction();

            $product->update($validated);

            DB::commit();

            $this->createProductNote($product, 'Producto "' . $product->name . '" actualizado el ' . now()->format('d/m/Y H:i') . ' por ' . Auth::user()->name);

            return [
                'success' => true,
                'product' => $product,
                'message' => 'Producto actualizado exitosamente.'
            ];
        } catch (\Illuminate\Validation\ValidationException $e) {
                DB::rollBack();
                return [
                    'success' => false,
                    'message' => 'Error de validación. Hay ' . count($e->errors()) . ' error(es).',
                    'type' => 'validation-exception',
                    'errors' => $e->errors(),
                ];
        } catch (\Exception $e) {
            DB::rollBack();
            return [
                'success' => false,
                'message' => 'Error al actualizar el producto: ' . $e->getMessage(),
                'type' => 'exception',
            ];
        }
    }

    public function deleteProduct(int $id): array
    {
        try {
            $product = Product::findOrFail($id);

            DB::beginTransaction();

            $product->delete();

            $this->createProductNote($product, 'Producto "' . $product->name . '" eliminado el ' . now()->format('d/m/Y H:i') . ' por ' . Auth::user()->name);

            DB::commit();

            return [
                'success' => true,
                'message' => 'Producto eliminado exitosamente.'
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            return [
                'success' => false,
                'message' => 'Error al eliminar el producto: ' . $e->getMessage(),
                'type' => 'exception',
            ];
        }
    }

    private function validateProductData(array $data): array
    {
        $rules = [
            'name' => 'required|string|max:255',
            'description' => 'required|string|max:1000',
        ];

        return validator($data, $rules)->validate();
    }
}
