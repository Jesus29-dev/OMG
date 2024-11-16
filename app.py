class Checklist:
    def __init__(self, id, name):
        self.id = id
        self.name = name
        self.questions = []
        self.answers = []

    def add_question(self, question):
        self.questions.append(question)

    def get_questions(self):
        return self.questions

class ChecklistFactory:
    def create_checklist(self, type):
        if type == "limpieza":
            return ChecklistLimpieza()
        elif type == "orden":
            return ChecklistOrden()
        else:
            raise ValueError("Tipo de checklist no válido")

class ChecklistLimpieza(Checklist):
    def __init__(self):
        super().__init__(1, "Checklist de Limpieza")
        self.add_question("¿El piso está limpio?")
        # ... otras preguntas

class ChecklistOrden(Checklist):
    def __init__(self):
        super().__init__(2, "Checklist de Orden")
        self.add_question("¿Las herramientas están en su lugar?")
        # ... otras preguntas
