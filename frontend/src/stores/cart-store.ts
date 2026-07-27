import { create } from 'zustand';

export interface CartLine {
  productId: number;
  name: string;
  sku: string;
  unitPrice: number;
  quantity: number;
  /** Available stock at the active branch when the line was added (for UI hints). */
  available: number;
}

interface CartState {
  lines: CartLine[];
  /** productIds flagged by a 409 InsufficientStock response. */
  shortages: Record<number, number>; // productId -> available
  add: (line: Omit<CartLine, 'quantity'>) => void;
  setQuantity: (productId: number, quantity: number) => void;
  remove: (productId: number) => void;
  clear: () => void;
  flagShortages: (shortages: { product_id: number; available: number }[]) => void;
  clearShortages: () => void;
}

export const useCartStore = create<CartState>((set) => ({
  lines: [],
  shortages: {},
  add: (line) =>
    set((state) => {
      const existing = state.lines.find((l) => l.productId === line.productId);
      if (existing) {
        return {
          lines: state.lines.map((l) =>
            l.productId === line.productId
              ? { ...l, quantity: l.quantity + 1 }
              : l,
          ),
        };
      }
      return { lines: [...state.lines, { ...line, quantity: 1 }] };
    }),
  setQuantity: (productId, quantity) =>
    set((state) => ({
      lines: state.lines
        .map((l) =>
          l.productId === productId
            ? { ...l, quantity: Math.max(0, quantity) }
            : l,
        )
        .filter((l) => l.quantity > 0),
    })),
  remove: (productId) =>
    set((state) => ({
      lines: state.lines.filter((l) => l.productId !== productId),
    })),
  clear: () => set({ lines: [], shortages: {} }),
  flagShortages: (shortages) =>
    set(() => ({
      shortages: Object.fromEntries(
        shortages.map((s) => [s.product_id, s.available]),
      ),
    })),
  clearShortages: () => set({ shortages: {} }),
}));
