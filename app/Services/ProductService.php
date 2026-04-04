<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Support\Facades\Auth;
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

            return [
                'success' => true,
                'product' => Product::create($validated),
                'message' => 'Producto creado exitosamente.'
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error al crear el producto: ' . $e->getMessage()
            ];
        }
    }

    public function updateProduct(int $id, array $data): array
    {
        try {
            $validated = $this->validateProductData($data);

            $product = Product::findOrFail($id);
            $product->update($validated);

            $this->createProductNote($product, 'Producto "' . $product->name . '" actualizado el ' . now()->format('d/m/Y H:i') . ' por ' . Auth::user()->name);

            return [
                'success' => true,
                'product' => $product,
                'message' => 'Producto actualizado exitosamente.'
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error al actualizar el producto: ' . $e->getMessage()
            ];
        }
    }

    public function deleteProduct(int $id): array
    {
        try {
            $product = Product::findOrFail($id);
            $product->delete();

            $this->createProductNote($product, 'Producto "' . $product->name . '" eliminado el ' . now()->format('d/m/Y H:i') . ' por ' . Auth::user()->name);

            return [
                'success' => true,
                'message' => 'Producto eliminado exitosamente.'
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error al eliminar el producto: ' . $e->getMessage()
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
