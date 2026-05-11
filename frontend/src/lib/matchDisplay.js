export function teamLabel(players) {
  return players.map((player) => player.firstName).join(' / ');
}

export function formatSets(sets) {
  return sets.map((set) => `${set.teamA}-${set.teamB}`).join('  ');
}
