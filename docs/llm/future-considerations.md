# Future Considerations

Ideas deliberately **not** part of the v1 scope documented elsewhere in this folder, captured here
so they aren't lost and can be picked up once the core feature ships.

## "Auto" model selection

**Question raised**: would an auto-mode for model selection — the system picking a model on the
user's behalf rather than the user always choosing manually — be beneficial and feasible?

**This does not contradict the "manual switching" decision recorded in
[`README.md`](README.md).** That decision rules out *silent* server-side routing the user never
sees or agrees to. An "Auto" option can instead be added as an explicit, clearly-labeled entry
*inside* the manual model picker — the user still makes a deliberate choice, they're just
choosing "let MetaGer pick for me" instead of a specific model. Functionally this is just another
row in `models.json`/the model registry whose "provider" is "pick one of the real providers at
request time" instead of a fixed provider — it flows through the exact same adapter/billing
pipeline as every other model (see
[`metager-chat-service/architecture.md`](metager-chat-service/architecture.md) and
[`metager-chat-service/billing.md`](metager-chat-service/billing.md)), so it's architecturally
cheap to add later without redesigning anything already planned.

**Why it could be worth it**:
- Removes choice paralysis for users who don't know or care about the difference between GPT-4o,
  Claude, and Mistral — probably most users.
- Gives MetaGer a lever to route cheap/simple messages to cheaper models and only reach for
  expensive models when needed, which — given the 2x-margin billing model in `billing.md` — could
  lower the effective cost users pay for typical usage, a real user-facing benefit, not just an
  infra optimization.
- Can double as a resilience mechanism (fall back to a different provider if one is rate-limited
  or down), which the fixed-model picker doesn't get for free.

**Why it's recommended for v2, not v1**:
- Requires an actual routing decision mechanism (heuristic or a cheap classification step), which
  adds latency and a new place for the system to make a wrong call (routing a genuinely hard
  question to a weak model gives a worse answer with no obvious recourse for the user).
- **Transparency requirement**: if adopted, the actual model/provider that answered a given
  message must always be visible per-message (already recommended in
  [`metager-integration/foki-integration.md`](metager-integration/foki-integration.md) §5
  regardless of Auto mode, but it becomes load-bearing once Auto exists — otherwise users can't
  tell which provider actually received their data for a given query).
- **Privacy corollary specific to MetaGer's positioning**: Auto mode quietly choosing between a US
  provider (OpenAI, Anthropic) and an EU one (Mistral) has different data-jurisdiction
  implications than a fixed manual choice. If Auto ships, it should very likely respect a
  user-configurable constraint (e.g. "only auto-route within EU providers") rather than treating
  all three as interchangeable purely on cost/quality grounds — otherwise Auto mode quietly
  undermines the reason a privacy-focused user picked Mistral as an option to begin with.
- Ongoing maintenance burden: routing policy (which queries go to which model) needs to be
  revisited as providers ship new models/pricing, unlike the fixed picker which is just a static
  catalog.

**Recommendation**: ship v1 with manual selection only, as already decided. Revisit Auto mode
once the core feature has real usage data to inform routing heuristics and cost-savings estimates,
and treat the transparency + provider-jurisdiction points above as requirements for that future
work, not optional polish.
