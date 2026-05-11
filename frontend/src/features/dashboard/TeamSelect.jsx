export function TeamSelect({ title, values, players, onChange }) {
  return (
    <fieldset className="team-select">
      <legend>{title}</legend>
      {values.map((value, index) => (
        <select
          key={index}
          value={value}
          onChange={(event) => {
            const next = [...values];
            next[index] = event.target.value;
            onChange(next);
          }}
          required
        >
          <option value="">Choisir un joueur</option>
          {players.map((player) => (
            <option key={player.id} value={player.id}>
              {player.fullName} · N{player.level}
            </option>
          ))}
        </select>
      ))}
    </fieldset>
  );
}
