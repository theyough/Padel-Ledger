export function ScoreSetRow({ index, set, updateSet }) {
  return (
    <>
      <span>Manche {index + 1}</span>
      <input type="number" min="0" max="7" value={set.teamA} onChange={(event) => updateSet(index, 'teamA', event.target.value)} />
      <input type="number" min="0" max="7" value={set.teamB} onChange={(event) => updateSet(index, 'teamB', event.target.value)} />
    </>
  );
}
