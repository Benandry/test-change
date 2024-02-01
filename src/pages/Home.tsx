import { FC } from "react";
import {
  Contact,
  Graduate,
  Homepage,
  Project,
  Skills,
  Tech,
  Who,
} from "../components";

const Home: FC = () => {
  return (
    <>
      <Homepage />
      <Who />
      <Skills />
      <Project />
      <Tech />
      <Graduate />
      <Contact />
    </>
  );
};

export default Home;
