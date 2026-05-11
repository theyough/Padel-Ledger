import { describe, expect, it } from 'vitest';
import { asCollection, asMatch, asPlayer } from './hydra.js';

describe('hydra helpers', () => {
  it('asCollection prefers member then hydra:member', () => {
    expect(asCollection({ member: [1, 2] })).toEqual([1, 2]);
    expect(asCollection({ 'hydra:member': [3] })).toEqual([3]);
    expect(asCollection({ raw: true })).toEqual({ raw: true });
  });

  it('asPlayer unwraps player key', () => {
    expect(asPlayer({ player: { id: 1 } })).toEqual({ id: 1 });
    expect(asPlayer({ id: 2 })).toEqual({ id: 2 });
  });

  it('asMatch unwraps match key', () => {
    expect(asMatch({ match: { id: 5 } })).toEqual({ id: 5 });
    expect(asMatch({ id: 6 })).toEqual({ id: 6 });
  });
});
