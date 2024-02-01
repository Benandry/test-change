import { FC } from "react";
import "./App.css";
import { styles } from "./styles";
import { Navbar } from "./components";
import { BrowserRouter as Router, Routes, Route } from "react-router-dom";

import Footer from "./components/Footer";
import Home from "./pages/Home";

const App: FC = () => {
  return (
    <Router>
      <div className="bg-primary w-full overflow-hidden fixed top-0">
        <div className={`${styles.paddingX} ${styles.flexCenter} border-nav`}>
          <div className={`${styles.boxWidth}`}>
            <Navbar />
          </div>
        </div>
      </div>

      <Routes>
        <Route index path="/" Component={Home} />
      </Routes>
      <div>
        <Footer />
      </div>
    </Router>
  );
};

export default App;
