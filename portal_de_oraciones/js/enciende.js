function waitForClick() {
  return new Promise(resolve => {
    const box = document.getElementById("debugPause");
    const btn = document.getElementById("btnContinue");

    box.style.display = "block";

    btn.onclick = () => {
      box.style.display = "none";
      resolve();
    };
  });
}