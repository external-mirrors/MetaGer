import { enhanceContents, enhanceRequestKinds } from "./removal/enhance";

const guide = document.querySelector("#removal");

if (guide !== null) {
  enhanceRequestKinds(guide);
  enhanceContents(guide);
}
