# Midjourney – Mobile/Web App Review (AI Technology)

Prepared for: HCI Assignment – Review Mobile Apps (AI Technology)  
App reviewed: Midjourney (AI image generation)  
Date: 2025-10-13

---

## 1) App Description (What it is, who made it, where it runs)
- Name: Midjourney
- Creator/Owner: Midjourney, Inc. (founded by David Holz)
- Purpose: Generate high‑quality images from natural‑language prompts using generative AI (diffusion/transformer‑based models). Typical use cases: concept art, product mockups, mood boards, posters, UI inspirations, backgrounds, illustration styles.
- Launch/Availability: Public beta in 2022 via Discord; paid plans from 2023; web app launched (alpha → production 2024/2025) with gallery, prompt history and creation tools.
- Platforms:
  - Discord bot (desktop + mobile) – primary interaction (chat‑based)
  - Web app – browse, prompt, upscale, variations, profiles
- Business model: Subscription (Fast/Relax modes, private mode on higher plans). Rate limits and queues apply.

---

## 2) Three Good Features (HCI strengths)
Below, each strength is mapped to HCI principles and why it helps users.

1. Fast, continuous feedback loop (Preview → Upscale/Variations)  
   - Principles: Feedback, Visibility of System Status, Direct Manipulation  
   - Why good: A single prompt returns 4 thumbnails quickly. Clear labeled buttons (U1–U4, V1–V4, Reroll) make next actions obvious. Users see progress messages and remaining Fast/Relax time (status visibility). This tight loop encourages exploration with low friction and keeps perceived latency low.

2. Progressive disclosure of power features  
   - Principles: Recognition over Recall, Progressive Disclosure, Learnability  
   - Why good: Beginners can type a plain sentence and click a button. Advanced users can add parameters (e.g., --stylize, --ar, --seed, --tile) or use the web prompt pane with style presets. Hiding complexity until needed reduces cognitive load while still supporting expert workflows.

3. Social discovery and inspiration (community galleries)  
   - Principles: External Cognition/Scaffolding, Social Proof, Learnability  
   - Why good: The public feed (Discord channels and web gallery) shows prompts + outputs in context. Users learn effective prompting patterns by example instead of memorizing syntax. This lowers entry barriers and accelerates skill acquisition.

---

## 3) Three Poor Features (HCI weaknesses)
1. Chat‑based UI noise on Discord (context switching + cognitive load)  
   - Principles: Minimize Cognitive Load, Information Scent, Attention Management  
   - Why bad: Images and system messages get buried in busy channels; finding a past result or its prompt is hard. On mobile Discord, the stream is especially overwhelming, harming wayfinding and session continuity.

2. Parameter discoverability and memorability  
   - Principles: Recognition over Recall, Affordance, Consistency  
   - Why bad: Power comes from parameters but they are learned from docs/videos or trial‑and‑error. Syntax (--stylize 750, --ar 3:2, --chaos 40) is compact but opaque; not self‑revealing without tooltips/controls. This pushes users back to recall rather than recognition.

3. Transparency about queue, limits, and rights is scattered  
   - Principles: Visibility of System Status, Error Prevention, Trust/Transparency  
   - Why bad: Fast/Relax consumption, queue position, and failed render reasons are not always surfaced at decision time (especially in Discord). Usage rights & attribution guidance exist but are not always available at the point of export, creating uncertainty in professional contexts.

---

## 4) Suggestions for Improvement (Concrete, actionable)
1. Dedicated “Prompt Builder” GUI (web + mobile)  
   - Sliders/toggles for aspect ratio, style strength, chaos, stylize, seed.  
   - Live preview of parameter effects using tiny draft renders.  
   - Principle: Recognition over Recall; Error Prevention.

2. Unified status panel  
   - Always‑visible queue state, plan quota, current Fast/Relax mode, and estimated time.  
   - Principle: Visibility of System Status; Locus of Control.

3. Task‑oriented templates  
   - Presets for common jobs: Product photo, Character sheet, Poster, Logo ideation, UI mock, Texture tile.  
   - Principle: Match with Real‑World Tasks; Progressive Disclosure.

4. Prompt history with semantic search + tags  
   - Search by visual style (“watercolor”, “isometric UI”), by parameter ranges, and by colors.  
   - Principle: External cognition; Reduce Memory Load.

5. Inline onboarding and tooltips  
   - Hover tips that show what --stylize, --chaos, --tile, etc. do with micro‑examples.  
   - Principle: Learnability; Recognition over Recall.

6. Mobile‑first improvements  
   - Two‑pane mobile layout (results + prompt), bigger tap targets, sticky action bar (Upscale/Variations/Bookmark) and swipe‑to‑compare.  
   - Principle: Fitts’s Law; Touch Affordances; Consistency.

7. Responsible‑use guardrails at export  
   - Inline reminders on commercial use, model limitations, and attribution; quick links to license pages.  
   - Principle: Error Prevention; Trust/Transparency.

---

## 5) Summary Judgment (Why good & why bad)
- Why good: Midjourney excels at fast feedback, high output quality, and community‑driven learning. The explore‑iterate loop is tight and rewarding, which strongly supports ideation and concept exploration.
- Why bad: The Discord‑first UX creates noise and retrieval pain; parameter power is hidden behind recall; operational transparency (queue/limits/rights) isn’t always surfaced when decisions are made. These frictions impact novice confidence and professional workflows.

---

## 6) Annex – Quick Facts
- Core tasks: Prompt → Preview grid → Upscale/Variations → Refine → Export  
- Competing tools: DALL·E 3 (natural language editing), Adobe Firefly (brand‑safe, PSD integration), Stable Diffusion (local control, ControlNet, LoRA)
- Risk notes: Prompt ambiguity, bias in training data, content policy constraints, license clarity per plan

---

## 7) HCI Principles Mapped (Checklist)
- Visibility of system status – PARTIAL (web better than Discord)
- Match between system and real world – GOOD (prompting, galleries)
- User control and freedom – GOOD (versions, upscale/variations)
- Consistency and standards – MIXED (Discord vs web)
- Error prevention – NEEDS WORK (parameter guardrails)
- Recognition rather than recall – NEEDS WORK (parameters UI)
- Flexibility and efficiency of use – GOOD (shortcuts, presets possible)
- Aesthetic and minimalist design – GOOD in web, BUSY in Discord
- Help and documentation – AVAILABLE, but not inline enough

---

### One‑Slide Executive Takeaway (for PPT)
- Strengths: quality, iteration speed, community learning.  
- Weaknesses: chat noise, parameter recall, transparency.  
- Do next: GUI prompt builder, unified status, searchable history.
