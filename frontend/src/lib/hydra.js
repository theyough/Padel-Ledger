export function asCollection(payload) {
  return payload.member || payload['hydra:member'] || payload;
}

export function asPlayer(payload) {
  return payload.player || payload;
}

export function asMatch(payload) {
  return payload.match || payload;
}
