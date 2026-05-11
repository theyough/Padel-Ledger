import { describe, expect, it } from 'vitest';
import { formatSets, teamLabel } from './matchDisplay.js';

describe('matchDisplay', () => {
  it('teamLabel joins first names', () => {
    expect(teamLabel([{ firstName: 'Ada' }, { firstName: 'Grace' }])).toBe('Ada / Grace');
  });

  it('formatSets renders dash-separated games', () => {
    expect(formatSets([{ teamA: 6, teamB: 4 }, { teamA: 7, teamB: 5 }])).toBe('6-4  7-5');
  });
});
